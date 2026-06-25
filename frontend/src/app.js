// frontend/src/app.js

import { AuthController } from "./controllers/AuthController.js";
import { DashboardController } from "./controllers/DashboardController.js";
import { ProfileController } from "./controllers/ProfileController.js";
import { MessageController } from "./controllers/MessageController.js";
import { AdminController } from "./controllers/AdminController.js";
import { PremiumController } from "./controllers/PremiumController.js";
import { getApiUrl, fetchJson } from "./config.js";

document.addEventListener("DOMContentLoaded", () => {
  console.log("Application lancée en mode POO/MVC !");

  if (
    document.getElementById("register-form") ||
    document.getElementById("login-form")
  ) {
    console.log("-> Mode Authentification activé");
    new AuthController();
  }

  if (document.getElementById("dashboard-section")) {
    console.log("-> Mode Dashboard activé");
    new DashboardController();
  }

  if (document.getElementById("profile-form")) {
    console.log("-> Mode Profil activé");
    new ProfileController();
  }

  if (document.getElementById("inbox-list")) {
    console.log("-> Mode Messagerie activé");
    new MessageController();
  }

  if (document.getElementById("admin-search-users")) {
    console.log("-> Mode Admin activé");
    new AdminController();
  }

  if (document.getElementById("premium-checkout-section")) {
    console.log("-> Mode Premium activé");
    new PremiumController();
  }

  const logoutButtons = document.querySelectorAll("#logout-btn, #mobile-logout-btn");
  logoutButtons.forEach((btn) => {
    btn.addEventListener("click", async (event) => {
      event.preventDefault();
      try {
        await fetchJson(`${getApiUrl()}?action=logout`, {
          method: "POST",
          credentials: "include",
        });
      } catch (error) {
        console.warn("Déconnexion impossible :", error);
      }
      window.location.href = "./login.html";
    });
  });
});
