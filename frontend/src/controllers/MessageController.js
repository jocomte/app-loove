// frontend/src/controllers/MessageController.js
import { MessageModel } from "../models/MessageModel.js";
import { getApiUrl, fetchJson } from "../config.js";

export class MessageController {
  constructor() {
    this.messageModel = new MessageModel();
    
    // Éléments du DOM
    this.inboxList = document.getElementById("inbox-list");
    this.conversationTitle = document.getElementById("conversation-title");
    this.conversationMessages = document.getElementById("conversation-messages");
    this.messageForm = document.getElementById("message-form");
    this.messageInput = document.getElementById("message-input");
    
    this.currentTargetId = null;
    this.pollingInterval = null;
    this.lastMessagesJson = "";

    if (this.inboxList) {
      this.loadInbox().then(() => {
        // Vérifier si un correspondant par défaut est spécifié dans l'URL (ex: suite à un Match)
        const urlParams = new URLSearchParams(window.location.search);
        const targetId = urlParams.get("target_id");
        if (targetId) {
          this.openConversation(parseInt(targetId));
        }
      });
    }

    if (this.messageForm) {
      this.messageForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!this.currentTargetId) return;

        const content = this.messageInput.value.trim();
        if (!content) return;

        try {
          await this.messageModel.sendMessage(this.currentTargetId, content);
          this.messageInput.value = "";
          
          // Charger immédiatement sans attendre le prochain tick du polling
          await this.loadConversation(this.currentTargetId, false);
          
          // Recharger la boîte de réception pour actualiser le dernier message/ordre
          this.loadInbox();
        } catch (error) {
          alert("Erreur lors de l'envoi : " + error.message);
        }
      });
    }

    // Gérer le bouton retour sur mobile et le signalement
    const backBtn = document.getElementById("chat-back-btn");
    const chatReportBtn = document.getElementById("chat-report-btn");
    const reportModal = document.getElementById("report-modal");
    const reportForm = document.getElementById("report-form");
    const reportCancelBtn = document.getElementById("report-cancel-btn");
    const reportReasonInput = document.getElementById("report-reason");

    if (backBtn) {
      backBtn.addEventListener("click", () => {
        this.stopPolling();
        this.currentTargetId = null;
        this.lastMessagesJson = "";
        
        const messagesSection = document.getElementById("messages-section");
        if (messagesSection) {
          messagesSection.classList.remove("chat-open");
        }
        if (chatReportBtn) {
          chatReportBtn.style.display = "none";
        }
        
        this.loadInbox();
      });
    }

    if (chatReportBtn) {
      chatReportBtn.addEventListener("click", () => {
        if (reportModal) {
          reportModal.classList.add("active");
        }
      });
    }

    if (reportCancelBtn) {
      reportCancelBtn.addEventListener("click", () => {
        if (reportModal) {
          reportModal.classList.remove("active");
        }
      });
    }

    if (reportForm) {
      reportForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const reason = reportReasonInput.value.trim();
        if (!reason || !this.currentTargetId) return;

        try {
          // Utilisation directe de fetchJson
          const apiResponse = await fetchJson(`${getApiUrl()}?action=submit-report`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ reported_id: this.currentTargetId, reason: reason }),
            credentials: "include"
          });

          alert(apiResponse.message || "Signalement enregistré avec succès.");
          reportModal.classList.remove("active");
          reportReasonInput.value = "";
          
          if (backBtn) {
            backBtn.click();
          }
        } catch (error) {
          alert("Erreur de signalement : " + error.message);
        }
      });
    }
  }

  /**
   * Charge la liste des discussions actives à gauche
   */
  async loadInbox() {
    try {
      const conversations = await this.messageModel.fetchInbox();
      this.inboxList.innerHTML = "";

      if (!conversations || conversations.length === 0) {
        this.inboxList.innerHTML = `<p style="padding: 20px; color: var(--color-text-muted); font-size: 14px;">Aucun match pour le moment. Allez sur le Dashboard pour aimer des profils !</p>`;
        return;
      }

      conversations.forEach((conversation) => {
        const item = document.createElement("button");
        item.type = "button";
        item.className = `conversation-item ${this.currentTargetId === conversation.partner_id ? "active" : ""}`;
        
        // Avatar du partenaire
        const avatarUrl = conversation.partner_photo || "";
        const avatarHTML = avatarUrl 
          ? `<img class="conversation-avatar" src="${avatarUrl}" alt="Avatar">`
          : `<div class="conversation-avatar" style="display:flex;align-items:center;justify-content:center;background:var(--gradient-primary);color:white;font-weight:700;font-size:18px;">${conversation.firstname[0]}</div>`;

        // Indicateur non lu
        const unreadCount = parseInt(conversation.unread_count || 0);
        const unreadIndicatorHTML = unreadCount > 0 
          ? `<span class="unread-indicator"></span>` 
          : "";

        item.innerHTML = `
          ${avatarHTML}
          <div class="conversation-details">
            <span class="conversation-name">${conversation.firstname} ${conversation.lastname}</span>
            <div class="conversation-meta">
              <span style="font-size: 12px; color: var(--color-text-muted);">Cliquez pour discuter</span>
              ${unreadIndicatorHTML}
            </div>
          </div>
        `;

        item.addEventListener("click", () => this.openConversation(conversation.partner_id));
        this.inboxList.appendChild(item);
      });
    } catch (error) {
      console.error("Erreur loadInbox:", error);
      this.inboxList.innerHTML = `<p style="padding: 20px; color: red;">Erreur: ${error.message}</p>`;
    }
  }

  /**
   * Sélectionne et lance la discussion avec un partenaire
   */
  async openConversation(targetId) {
    this.currentTargetId = targetId;
    
    // Mettre à jour l'état actif dans la liste sans recharger tout le DOM
    const items = this.inboxList.querySelectorAll(".conversation-item");
    items.forEach((item, index) => {
      // On recharge la liste pour être propre ou on toggle juste la classe
    });
    this.loadInbox();

    const messagesSection = document.getElementById("messages-section");
    if (messagesSection) {
      messagesSection.classList.add("chat-open");
    }

    const chatReportBtn = document.getElementById("chat-report-btn");
    if (chatReportBtn) {
      chatReportBtn.style.display = "block";
    }

    // Arrêter le polling précédent s'il y en avait un
    this.stopPolling();

    // Charger une première fois de manière forcée (sans polling asynchrone)
    await this.loadConversation(targetId, false);

    // Lancer le polling intelligent toutes les 3 secondes
    this.startPolling(targetId);
  }

  /**
   * Charge les messages de la conversation active
   * @param {number} targetId ID du correspondant
   * @param {boolean} isPolling Indique si l'appel est automatique en arrière-plan
   */
  async loadConversation(targetId, isPolling = false) {
    try {
      const messages = await this.messageModel.fetchConversation(targetId);
      
      // Si la réponse est vide, on s'assure qu'on affiche un titre par défaut
      if (!messages || messages.length === 0) {
        this.conversationTitle.textContent = "Discussion";
        this.conversationMessages.innerHTML = `<p style="text-align: center; color: var(--color-text-muted); margin-top: 50px;">Envoyez un message pour démarrer la discussion !</p>`;
        return;
      }

      // Comparer le nouveau contenu avec l'ancien pour éviter les clignotements et le scroll forcé
      const messagesJson = JSON.stringify(messages);
      if (isPolling && messagesJson === this.lastMessagesJson) {
        return; // Rien n'a changé, on ne touche à rien
      }

      // Mettre à jour le cache d'état
      this.lastMessagesJson = messagesJson;

      this.conversationTitle.textContent = `Discussion avec ${messages[0].partner_name}`;
      this.conversationMessages.innerHTML = "";

      messages.forEach((message) => {
        if (message.content) {
          const line = document.createElement("div");
          line.className = message.is_sender == 1 ? "message-sent" : "message-received";
          line.textContent = message.content;
          this.conversationMessages.appendChild(line);
        }
      });

      // Défiler automatiquement vers le bas
      this.conversationMessages.scrollTop = this.conversationMessages.scrollHeight;

      // Si le chargement a lieu suite à du polling, on rafraîchit la boîte de réception à gauche 
      // pour effacer les indicateurs de messages non lus qui viennent d'être ouverts
      if (isPolling) {
        this.loadInbox();
      }
    } catch (error) {
      console.error("Erreur de chargement de la conversation :", error);
      if (!isPolling) {
        this.conversationMessages.innerHTML = `<p style="text-align: center; color: red;">Impossible de charger la conversation.</p>`;
      }
    }
  }

  /**
   * Démarre le polling automatique
   */
  startPolling(targetId) {
    this.pollingInterval = setInterval(() => {
      this.loadConversation(targetId, true);
    }, 3000);
  }

  /**
   * Arrête le polling automatique
   */
  stopPolling() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
      this.pollingInterval = null;
    }
  }
}
