// frontend/src/controllers/AuthController.js

import { AuthModel } from "../models/AuthModel.js";

export class AuthController {
  constructor() {
    this.authModel = new AuthModel();
    this.feedbackContainer = document.getElementById("form-feedback");

    // Formulaires
    this.registerForm = document.getElementById("register-form");
    this.loginForm = document.getElementById("login-form");
    this.verifyForm = document.getElementById("verify-form");

    if (this.registerForm) this.initRegisterEvent();
    if (this.loginForm) this.initLoginEvent();
    if (this.verifyForm) this.initVerifyEvent();
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

        // Redirection vers le dashboard après 1 seconde
        setTimeout(() => {
          window.location.href = "./dashboard.html";
        }, 1000);
      } catch (error) {
        this.feedbackContainer.textContent = error.message;
        this.feedbackContainer.style.color = "red";
      }
    });
  }

  // 📝 Méthode d'inscription avec passage à la vérification d'email
  initRegisterEvent() {
    this.registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const data = Object.fromEntries(
        new FormData(this.registerForm).entries(),
      );

      try {
        this.feedbackContainer.textContent = "Inscription en cours...";
        this.feedbackContainer.style.backgroundColor = "#e8f4f8";
        this.feedbackContainer.style.color = "#2980b9";

        const response = await this.authModel.registerUser(data);

        if (response.requires_verification) {
          // Affichage du panneau de vérification
          document.getElementById("register-section").style.display = "none";
          const verifySection = document.getElementById("verification-section");
          if (verifySection) verifySection.style.display = "block";

          document.getElementById("verify-email").value = response.email;
          const demoBox = document.getElementById("demo-code-box");
          if (demoBox && response.dev_code) {
            demoBox.textContent = `📧 [Mode Démo Soutenance / Logs] Code généré : ${response.dev_code}`;
          }
        }
      } catch (error) {
        // Affichage de l'erreur stylisée
        this.feedbackContainer.textContent = error.message;
        this.feedbackContainer.style.backgroundColor = "#fdedec";
        this.feedbackContainer.style.color = "#c0392b";
      }
    });
  }

  initVerifyEvent() {
    this.verifyForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const feedback = document.getElementById("verify-feedback");
      const email = document.getElementById("verify-email").value;
      const code = document.getElementById("verify-code").value;

      try {
        feedback.textContent = "Vérification en cours...";
        feedback.style.color = "#2980b9";

        const response = await this.authModel.verifyEmail(email, code);

        feedback.textContent = response.message + " Redirection...";
        feedback.style.color = "#27ae60";

        if (response.user) {
          localStorage.setItem("user", JSON.stringify(response.user));
        }

        setTimeout(() => {
          window.location.href = "./dashboard.html";
        }, 1200);
      } catch (error) {
        feedback.textContent = error.message;
        feedback.style.color = "#c0392b";
      }
    });
  }
}

