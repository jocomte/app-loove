// frontend/src/controllers/MessageController.js
import { MessageModel } from "../models/MessageModel.js";

export class MessageController {
  constructor() {
    this.messageModel = new MessageModel();
    this.inboxList = document.getElementById("inbox-list");
    this.conversationTitle = document.getElementById("conversation-title");
    this.conversationMessages = document.getElementById(
      "conversation-messages",
    );
    this.messageForm = document.getElementById("message-form");
    this.messageInput = document.getElementById("message-input");
    this.currentTargetId = null;

    if (this.inboxList) {
      this.loadInbox();
    }

    if (this.messageForm) {
      this.messageForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!this.currentTargetId) return;

        try {
          await this.messageModel.sendMessage(
            this.currentTargetId,
            this.messageInput.value.trim(),
          );
          this.messageInput.value = "";
          this.loadConversation(this.currentTargetId);
        } catch (error) {
          alert(error.message);
        }
      });
    }
  }

  async loadInbox() {
    try {
      const conversations = await this.messageModel.fetchInbox();
      this.inboxList.innerHTML = "";

      if (conversations.length === 0) {
        this.inboxList.innerHTML = `<p>Aucune conversation pour l'instant.</p>`;
        return;
      }

      conversations.forEach((conversation) => {
        const item = document.createElement("button");
        item.type = "button";
        item.className = "conversation-item";
        item.innerHTML = `<strong>${conversation.firstname} ${conversation.lastname}</strong> <span>${conversation.unread_count || 0} nouveaux</span>`;
        item.addEventListener("click", () =>
          this.openConversation(conversation.partner_id),
        );
        this.inboxList.appendChild(item);
      });
    } catch (error) {
      console.error(error);
      this.inboxList.innerHTML = `<p>Impossible de charger les conversations.</p>`;
    }
  }

  async openConversation(targetId) {
    this.currentTargetId = targetId;
    await this.loadConversation(targetId);
  }

  async loadConversation(targetId) {
    try {
      const messages = await this.messageModel.fetchConversation(targetId);
      if (!messages.length) {
        this.conversationTitle.textContent =
          "Aucune conversation sélectionnée.";
        this.conversationMessages.innerHTML = "";
        return;
      }

      this.conversationTitle.textContent = `Conversation avec ${messages[0].partner_name}`;
      this.conversationMessages.innerHTML = "";

      messages.forEach((message) => {
        const line = document.createElement("div");
        line.className = message.is_sender
          ? "message-sent"
          : "message-received";
        line.textContent = message.content;
        this.conversationMessages.appendChild(line);
      });
    } catch (error) {
      console.error(error);
      this.conversationMessages.innerHTML = `<p>Impossible de charger la conversation.</p>`;
    }
  }
}
