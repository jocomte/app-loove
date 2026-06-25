// frontend/src/controllers/PremiumController.js
import { getApiUrl, fetchJson } from "../config.js";

export class PremiumController {
  constructor() {
    this.apiUrl = getApiUrl();
    this.form = document.getElementById("premium-payment-form");
    this.feedbackContainer = document.getElementById("form-feedback");
    
    // Payment method inputs
    this.methodRadios = document.getElementsByName("payment_method");
    this.cardInputs = document.getElementById("card-inputs");

    if (this.form) {
      this.initEvents();
    }
  }

  initEvents() {
    // Show/hide credit card input block depending on chosen payment method
    Array.from(this.methodRadios).forEach((radio) => {
      radio.addEventListener("change", (e) => {
        if (e.target.value === "paypal") {
          this.cardInputs.style.display = "none";
          this.toggleRequiredCardInputs(false);
        } else {
          this.cardInputs.style.display = "block";
          this.toggleRequiredCardInputs(true);
        }
      });
    });

    // Formatting fields
    const cardNumberInput = document.getElementById("card-number");
    if (cardNumberInput) {
      cardNumberInput.addEventListener("input", (e) => {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formatted = "";
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) formatted += " ";
          formatted += value[i];
        }
        e.target.value = formatted;
      });
    }

    const cardExpiryInput = document.getElementById("card-expiry");
    if (cardExpiryInput) {
      cardExpiryInput.addEventListener("input", (e) => {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        if (value.length > 2) {
          e.target.value = value.substring(0, 2) + "/" + value.substring(2, 4);
        } else {
          e.target.value = value;
        }
      });
    }

    this.form.addEventListener("submit", async (e) => {
      e.preventDefault();
      
      const method = Array.from(this.methodRadios).find(r => r.checked)?.value;
      
      if (this.feedbackContainer) {
        this.feedbackContainer.textContent = "Validation du paiement sécurisé en cours...";
        this.feedbackContainer.className = "feedback-info";
        this.feedbackContainer.style.display = "block";
      }

      // Simulate payment delay
      setTimeout(async () => {
        try {
          const res = await fetchJson(`${this.apiUrl}?action=upgrade-premium`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ method: method }),
            credentials: "include"
          });

          if (this.feedbackContainer) {
            this.feedbackContainer.textContent = res.message;
            this.feedbackContainer.className = "feedback-success";
          }

          // Redirect to profile after short delay
          setTimeout(() => {
            window.location.href = "./profile.html";
          }, 2000);

        } catch (error) {
          if (this.feedbackContainer) {
            this.feedbackContainer.textContent = "Erreur de paiement : " + error.message;
            this.feedbackContainer.className = "feedback-error";
          }
        }
      }, 1500);
    });
  }

  toggleRequiredCardInputs(isRequired) {
    const inputs = this.cardInputs.querySelectorAll("input");
    inputs.forEach((input) => {
      if (isRequired) {
        input.setAttribute("required", "required");
      } else {
        input.removeAttribute("required");
      }
    });
  }
}
