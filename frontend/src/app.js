// frontend/src/app.js

import { AuthController } from "./controllers/AuthController.js";
import { DashboardController } from "./controllers/DashboardController.js";
import { ProfileController } from "./controllers/ProfileController.js";
import { MessageController } from "./controllers/MessageController.js";

document.addEventListener("DOMContentLoaded", () => {
  console.log("Application lancée en mode POO/MVC !");

  // 1. On active l'authentification uniquement si on est sur index.html ou login.html
  if (
    document.getElementById("register-form") ||
    document.getElementById("login-form")
  ) {
    console.log("-> Mode Authentification activé");
    new AuthController();
  }

  // 2. On active le dashboard uniquement si on est sur dashboard.html
  if (document.getElementById("dashboard-section")) {
    console.log("-> Mode Dashboard activé");
    new DashboardController();
  }

  // 3. On active le profil uniquement si on est sur profile.html
  if (document.getElementById("profile-form")) {
    console.log("-> Mode Profil activé");
    new ProfileController();
  }

  // 4. On active la messagerie uniquement si on est sur messages.html
  if (document.getElementById("inbox-list")) {
    console.log("-> Mode Messagerie activé");
    new MessageController();
  }
});
