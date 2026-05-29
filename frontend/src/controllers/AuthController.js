// frontend/src/controllers/AuthController.js

import { AuthModel } from "../models/AuthModel.js";

export class AuthController {
  // Mets à jour le constructor et ajoute la logique de login dans frontend/src/controllers/AuthController.js

  constructor() {
    this.authModel = new AuthModel();
    this.feedbackContainer = document.getElementById("form-feedback");

    // Formulaires
    this.registerForm = document.getElementById("register-form");
    this.loginForm = document.getElementById("login-form");

    if (this.registerForm) this.initRegisterEvent();
    if (this.loginForm) this.initLoginEvent();
  }

  initRegisterEvent() {
    this.registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(
        new FormData(this.registerForm).entries(),
      );
      try {
        const response = await this.authModel.registerUser(data);
        this.feedbackContainer.textContent = response.message;
        this.feedbackContainer.style.color = "green";
        this.registerForm.reset();
      } catch (error) {
        this.feedbackContainer.textContent = error.message;
        this.feedbackContainer.style.color = "red";
      }
    });
  }

  initLoginEvent() {
    this.loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(this.loginForm).entries());
      try {
        const response = await this.authModel.loginUser(data);
        this.feedbackContainer.textContent = response.message;
        this.feedbackContainer.style.color = "green";

        // Stockage basique de la session côté client
        localStorage.setItem("user", JSON.stringify(response.user));

        // Redirection vers le futur dashboard des matchs après 1 seconde
        setTimeout(() => {
          window.location.href = "./dashboard.html";
        }, 1000);
      } catch (error) {
        this.feedbackContainer.textContent = error.message;
        this.feedbackContainer.style.color = "red";
      }
    });
  }

  // Dans frontend/src/controllers/AuthController.js, remplace la méthode initRegisterEvent par celle-ci :

  initRegisterEvent() {
    this.registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const data = Object.fromEntries(
        new FormData(this.registerForm).entries(),
      );

      try {
        localStorage.setItem(
          "user",
          JSON.stringify({ firstname: data.firstname }),
        );
        this.feedbackContainer.textContent = "Inscription en cours...";
        this.feedbackContainer.style.backgroundColor = "#e8f4f8";
        this.feedbackContainer.style.color = "#2980b9";

        const response = await this.authModel.registerUser(data);

        // Affichage du succès stylisé
        this.feedbackContainer.textContent =
          response.message + " Redirection...";
        this.feedbackContainer.style.backgroundColor = "#e8f8f5";
        this.feedbackContainer.style.color = "#27ae60";
        this.registerForm.reset();

        // 🚀 REDIRECTION AUTOMATIQUE VERS LE DASHBOARD APRÈS 1.5 SECONDE
        setTimeout(() => {
          window.location.href = "./dashboard.html";
        }, 1500);
      } catch (error) {
        // Affichage de l'erreur stylisée
        this.feedbackContainer.textContent = error.message;
        this.feedbackContainer.style.backgroundColor = "#fdedec";
        this.feedbackContainer.style.color = "#c0392b";
      }
    });
  }
}
