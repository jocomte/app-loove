// frontend/src/controllers/AdminController.js
import { getApiUrl, fetchJson } from "../config.js";

export class AdminController {
  constructor() {
    this.apiUrl = getApiUrl();
    
    // Tab Elements
    this.tabUsersBtn = document.getElementById("tab-users-btn");
    this.tabReportsBtn = document.getElementById("tab-reports-btn");
    this.tabUsersContent = document.getElementById("tab-users-content");
    this.tabReportsContent = document.getElementById("tab-reports-content");

    // Stat Elements
    this.statTotalUsers = document.getElementById("stat-total-users");
    this.statPremiumUsers = document.getElementById("stat-premium-users");
    this.statRevenue = document.getElementById("stat-revenue");
    this.statPendingReports = document.getElementById("stat-pending-reports");

    // Lists & Search
    this.usersListContainer = document.getElementById("admin-users-list");
    this.reportsListContainer = document.getElementById("admin-reports-list");
    this.searchUsersInput = document.getElementById("admin-search-users");
    this.feedbackContainer = document.getElementById("form-feedback");

    this.allUsers = [];
    this.allReports = [];

    this.checkAdminAndInit();
  }

  async checkAdminAndInit() {
    try {
      const result = await fetchJson(`${this.apiUrl}?action=get-profile`, {
        method: "GET",
        credentials: "include",
      });

      if (!result.user || result.user.is_admin != 1) {
        alert("Accès refusé. Réservé aux administrateurs.");
        window.location.href = "./dashboard.html";
        return;
      }

      this.initEvents();
      this.loadDashboard();
    } catch (err) {
      console.error("Admin verification failed:", err);
      window.location.href = "./dashboard.html";
    }
  }

  initEvents() {
    // Tabs switching
    if (this.tabUsersBtn && this.tabReportsBtn) {
      this.tabUsersBtn.addEventListener("click", () => this.switchTab("users"));
      this.tabReportsBtn.addEventListener("click", () => this.switchTab("reports"));
    }

    // Search filtering
    if (this.searchUsersInput) {
      this.searchUsersInput.addEventListener("input", () => this.filterUsers());
    }
  }

  switchTab(tab) {
    this.tabUsersBtn.classList.remove("active");
    this.tabReportsBtn.classList.remove("active");
    this.tabUsersContent.style.display = "none";
    this.tabReportsContent.style.display = "none";

    // Style helper for tabs
    this.tabUsersBtn.style.color = "var(--color-text-muted)";
    this.tabUsersBtn.style.borderBottomColor = "transparent";
    this.tabReportsBtn.style.color = "var(--color-text-muted)";
    this.tabReportsBtn.style.borderBottomColor = "transparent";

    if (tab === "users") {
      this.tabUsersBtn.classList.add("active");
      this.tabUsersBtn.style.color = "#fd267d";
      this.tabUsersBtn.style.borderBottomColor = "#fd267d";
      this.tabUsersContent.style.display = "block";
    } else {
      this.tabReportsBtn.classList.add("active");
      this.tabReportsBtn.style.color = "#fd267d";
      this.tabReportsBtn.style.borderBottomColor = "#fd267d";
      this.tabReportsContent.style.display = "block";
    }
  }

  async loadDashboard() {
    this.loadStats();
    this.loadUsers();
    this.loadReports();
  }

  async loadStats() {
    try {
      const res = await fetchJson(`${this.apiUrl}?action=admin-stats`, {
        method: "GET",
        credentials: "include"
      });
      if (res.stats) {
        this.statTotalUsers.textContent = res.stats.total_users;
        this.statPremiumUsers.textContent = res.stats.premium_users;
        this.statRevenue.textContent = `${res.stats.revenue} €`;
        this.statPendingReports.textContent = res.stats.pending_reports;
      }
    } catch (error) {
      this.showFeedback(error.message, "error");
    }
  }

  async loadUsers() {
    try {
      const res = await fetchJson(`${this.apiUrl}?action=admin-users`, {
        method: "GET",
        credentials: "include"
      });
      if (res.users) {
        this.allUsers = res.users;
        this.renderUsers(this.allUsers);
      }
    } catch (error) {
      this.showFeedback("Erreur chargement utilisateurs : " + error.message, "error");
    }
  }

  renderUsers(users) {
    this.usersListContainer.innerHTML = "";
    if (users.length === 0) {
      this.usersListContainer.innerHTML = `<tr><td colspan="7" style="padding: 20px; text-align: center; color: var(--color-text-muted);">Aucun utilisateur trouvé.</td></tr>`;
      return;
    }

    users.forEach((u) => {
      const tr = document.createElement("tr");
      tr.style.borderBottom = "1px solid var(--color-border)";
      
      const genderLabel = u.gender === "male" ? "Homme" : u.gender === "female" ? "Femme" : "Autre";
      const orientationLabel = u.orientation === "men" ? "Hommes" : u.orientation === "women" ? "Femmes" : "Tout le monde";
      const premiumBadge = u.is_premium == 1 
        ? `<span style="background: #ffeef2; color: #fd267d; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 12px; border: 1px solid rgba(253,38,125,0.15);">★ PREMIUM</span>` 
        : `<span style="background: #f1f2f6; color: #57606f; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 12px;">Standard</span>`;

      const activeBadge = u.is_active == 1
        ? `<span style="background: #e8f8f5; color: #27ae60; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 12px;">Actif</span>`
        : `<span style="background: #fdedec; color: #c0392b; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 12px;">Banni</span>`;

      const activeActionBtn = u.is_active == 1
        ? `<button type="button" class="btn-ban" style="width: auto; padding: 6px 12px; font-size: 12px; background: #ff7f00; box-shadow: none; margin-right: 5px;">Bannir</button>`
        : `<button type="button" class="btn-unban" style="width: auto; padding: 6px 12px; font-size: 12px; background: #27ae60; box-shadow: none; margin-right: 5px;">Débannir</button>`;

      const premiumActionBtn = u.is_premium == 1
        ? `<button type="button" class="btn-toggle-premium" style="width: auto; padding: 6px 12px; font-size: 12px; background: #57606f; box-shadow: none; margin-right: 5px;">Standard</button>`
        : `<button type="button" class="btn-toggle-premium" style="width: auto; padding: 6px 12px; font-size: 12px; background: #f093fb; box-shadow: none; margin-right: 5px;">Premium</button>`;

      tr.innerHTML = `
        <td style="padding: 15px 20px; font-weight: 700;">${u.id}</td>
        <td style="padding: 15px 20px; font-weight: 600;">${u.firstname} ${u.lastname}</td>
        <td style="padding: 15px 20px;">${u.email}</td>
        <td style="padding: 15px 20px; color: var(--color-text-muted); font-size: 13px;">${genderLabel} cherche ${orientationLabel}</td>
        <td style="padding: 15px 20px;">${premiumBadge}</td>
        <td style="padding: 15px 20px;">${activeBadge}</td>
        <td style="padding: 15px 20px; text-align: right; white-space: nowrap;">
          ${premiumActionBtn}
          ${activeActionBtn}
          <button type="button" class="btn-delete-user btn-delete" style="width: auto; padding: 6px 12px; font-size: 12px; margin: 0;">Supprimer</button>
        </td>
      `;

      // Event Listeners for actions
      tr.querySelector(".btn-toggle-premium").addEventListener("click", () => this.updateUser(u.id, "toggle_premium"));
      const banBtn = tr.querySelector(".btn-ban");
      const unbanBtn = tr.querySelector(".btn-unban");
      if (banBtn) banBtn.addEventListener("click", () => this.updateUser(u.id, "ban"));
      if (unbanBtn) unbanBtn.addEventListener("click", () => this.updateUser(u.id, "unban"));
      tr.querySelector(".btn-delete-user").addEventListener("click", () => {
        if (confirm(`Voulez-vous vraiment supprimer définitivement le compte de ${u.firstname} ${u.lastname} ?`)) {
          this.updateUser(u.id, "delete");
        }
      });

      this.usersListContainer.appendChild(tr);
    });
  }

  filterUsers() {
    const query = this.searchUsersInput.value.toLowerCase().trim();
    if (!query) {
      this.renderUsers(this.allUsers);
      return;
    }

    const filtered = this.allUsers.filter((u) => {
      return u.firstname.toLowerCase().includes(query) ||
             u.lastname.toLowerCase().includes(query) ||
             u.email.toLowerCase().includes(query);
    });
    this.renderUsers(filtered);
  }

  async updateUser(userId, actionType) {
    try {
      const res = await fetchJson(`${this.apiUrl}?action=admin-update-user`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: userId, action_type: actionType }),
        credentials: "include"
      });

      this.showFeedback(res.message, "success");
      this.loadDashboard(); // Refresh data
    } catch (error) {
      this.showFeedback(error.message, "error");
    }
  }

  async loadReports() {
    try {
      const res = await fetchJson(`${this.apiUrl}?action=admin-reports`, {
        method: "GET",
        credentials: "include"
      });
      if (res.reports) {
        this.allReports = res.reports;
        this.renderReports(this.allReports);
      }
    } catch (error) {
      this.showFeedback("Erreur chargement signalements : " + error.message, "error");
    }
  }

  renderReports(reports) {
    this.reportsListContainer.innerHTML = "";
    if (reports.length === 0) {
      this.reportsListContainer.innerHTML = `<tr><td colspan="6" style="padding: 20px; text-align: center; color: var(--color-text-muted);">Aucun signalement.</td></tr>`;
      return;
    }

    reports.forEach((r) => {
      const tr = document.createElement("tr");
      tr.style.borderBottom = "1px solid var(--color-border)";

      const formattedDate = new Date(r.created_at).toLocaleDateString("fr-FR", {
        day: "numeric",
        month: "short",
        hour: "2-digit",
        minute: "2-digit"
      });

      let statusBadge = "";
      if (r.status === "pending") {
        statusBadge = `<span style="background: #ebf5fb; color: #2980b9; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 12px;">En attente</span>`;
      } else if (r.status === "resolved") {
        statusBadge = `<span style="background: #e8f8f5; color: #27ae60; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 12px;">Résolu</span>`;
      } else {
        statusBadge = `<span style="background: #f1f2f6; color: #57606f; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 12px;">Rejeté</span>`;
      }

      const actionsHTML = r.status === "pending"
        ? `
          <button type="button" class="btn-dismiss-rep" style="width: auto; padding: 6px 12px; font-size: 12px; background: #57606f; box-shadow: none; margin-right: 5px;">Rejeter</button>
          <button type="button" class="btn-resolve-rep" style="width: auto; padding: 6px 12px; font-size: 12px; background: #27ae60; box-shadow: none; margin-right: 5px;">Résoudre</button>
          <button type="button" class="btn-ban-rep btn-delete" style="width: auto; padding: 6px 12px; font-size: 12px; margin: 0;">Bannir l'auteur</button>
        `
        : `<span style="color: var(--color-text-muted); font-size: 13px;">Traité</span>`;

      tr.innerHTML = `
        <td style="padding: 15px 20px; color: var(--color-text-muted); font-size: 13px;">${formattedDate}</td>
        <td style="padding: 15px 20px; font-weight: 600;">${r.reporter_firstname} ${r.reporter_lastname} (ID: ${r.reporter_id})</td>
        <td style="padding: 15px 20px; font-weight: 600; color: #ff4757;">${r.reported_firstname} ${r.reported_lastname} (ID: ${r.reported_id})</td>
        <td style="padding: 15px 20px; font-style: italic; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${r.reason}">"${r.reason}"</td>
        <td style="padding: 15px 20px;">${statusBadge}</td>
        <td style="padding: 15px 20px; text-align: right; white-space: nowrap;">
          ${actionsHTML}
        </td>
      `;

      if (r.status === "pending") {
        tr.querySelector(".btn-dismiss-rep").addEventListener("click", () => this.updateReport(r.id, "dismiss"));
        tr.querySelector(".btn-resolve-rep").addEventListener("click", () => this.updateReport(r.id, "resolve"));
        tr.querySelector(".btn-ban-rep").addEventListener("click", () => {
          if (confirm(`Voulez-vous vraiment bannir l'utilisateur signalé (${r.reported_firstname} ${r.reported_lastname}) ?`)) {
            this.updateReport(r.id, "ban_reported");
          }
        });
      }

      this.reportsListContainer.appendChild(tr);
    });
  }

  async updateReport(reportId, actionType) {
    try {
      const res = await fetchJson(`${this.apiUrl}?action=admin-update-report`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ report_id: reportId, action_type: actionType }),
        credentials: "include"
      });

      this.showFeedback(res.message, "success");
      this.loadDashboard(); // Refresh data
    } catch (error) {
      this.showFeedback(error.message, "error");
    }
  }

  showFeedback(message, type) {
    if (!this.feedbackContainer) return;
    this.feedbackContainer.textContent = message;
    this.feedbackContainer.className = type === "success" ? "feedback-success" : "feedback-error";
    this.feedbackContainer.style.display = "block";

    setTimeout(() => {
      this.feedbackContainer.style.display = "none";
    }, 5000);
  }
}
