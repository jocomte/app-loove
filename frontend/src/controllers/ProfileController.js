// frontend/src/controllers/ProfileController.js
import { getApiUrl, fetchWithTimeout } from "../config.js";
import { PhotoModel } from "../models/PhotoModel.js";

export class ProfileController {
  constructor() {
    this.apiUrl = getApiUrl();
    this.photoModel = new PhotoModel();
    this.form = document.getElementById("profile-form");
    this.photoForm = document.getElementById("photo-form");
    this.photoInput = document.getElementById("photo-input");
    this.photoGallery = document.getElementById("photo-gallery");
    this.feedbackContainer = document.getElementById("form-feedback");
    this.photoFeedback = document.getElementById("photo-feedback");
    this.premiumBadge = document.getElementById("premium-badge");
    this.cityInput = document.getElementById("city");

    if (this.form) {
      this.loadUserData();
      this.loadPhotos();
      this.initEvents();
    }
  }

  /**
   * Récupère les données de la session actuelle pour pré-remplir le formulaire
   */
  async loadUserData() {
    try {
      const response = await fetchWithTimeout(`${this.apiUrl}?action=get-profile`, {
        method: "GET",
        credentials: "include", // 🚀 Vérifie la session sur le serveur avec la session PHP
      }, 15000);
      const result = await response.json();

      if (response.ok && result.user) {
        document.getElementById("firstname").value = result.user.firstname;
        document.getElementById("lastname").value = result.user.lastname;
        document.getElementById("bio").value = result.user.bio
          ? result.user.bio
          : "";
        document.getElementById("relationship_type").value =
          result.user.relationship_type;

        if (this.cityInput && result.user.latitude && result.user.longitude) {
          const lat = parseFloat(result.user.latitude);
          const lng = parseFloat(result.user.longitude);
          if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            this.cityInput.placeholder =
              `Position enregistrée (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
          }
        }

        // Si l'utilisateur est premium, on affiche le badge gold
        if (result.user.is_premium == 1) {
          this.premiumBadge.style.display = "inline-block";
        }
      } else {
        // Si pas connecté, redirection login
        window.location.href = "./login.html";
      }
    } catch (error) {
      console.error("Erreur de récupération utilisateur:", error);
    }
  }

  async loadPhotos() {
    if (!this.photoGallery) return;

    try {
      const photos = await this.photoModel.fetchPhotos();
      this.photoGallery.innerHTML = "";

      if (!photos.length) {
        this.photoGallery.innerHTML = "<p>Aucune photo ajoutée pour le moment.</p>";
        return;
      }

      photos.forEach((photo) => {
        const img = document.createElement("img");
        img.src = photo.url;
        img.alt = "Photo de profil";
        img.className = "profile-photo";
        if (photo.is_main == 1) {
          img.title = "Photo principale";
        }
        this.photoGallery.appendChild(img);
      });
    } catch (error) {
      console.error("Erreur chargement photos:", error);
      this.photoGallery.innerHTML = "<p>Impossible de charger les photos.</p>";
    }
  }

  initEvents() {
    if (this.photoForm) {
      this.photoForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(this.photoForm);

        try {
          const result = await this.photoModel.uploadPhoto(formData);
          this.photoFeedback.textContent = result.message;
          this.photoFeedback.style.color = "green";
          this.photoForm.reset();
          this.loadPhotos();
        } catch (error) {
          this.photoFeedback.textContent = error.message;
          this.photoFeedback.style.color = "red";
        }
      });
    }

    this.form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(this.form);
      const data = Object.fromEntries(formData.entries());

      if (this.cityInput && this.cityInput.value.trim()) {
        try {
          const coordinates = await this.geocodeCity(this.cityInput.value.trim());
          if (!coordinates) {
            throw new Error(
              "Impossible de trouver cette ville. Vérifiez l'orthographe et réessayez."
            );
          }

          data.latitude = coordinates.lat;
          data.longitude = coordinates.lng;
        } catch (error) {
          this.feedbackContainer.textContent = error.message;
          this.feedbackContainer.style.backgroundColor = "#fdedec";
          this.feedbackContainer.style.color = "#c0392b";
          this.feedbackContainer.style.border = "1px solid #f5b7b1";
          return;
        }
      }

      try {
        const response = await fetchWithTimeout(`${this.apiUrl}?action=update-profile`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
          credentials: "include", // 🚀 Permet la modification uniquement si la session correspond
        }, 15000);

        const result = await response.json();

        if (response.ok) {
          this.feedbackContainer.textContent = result.message;
          this.feedbackContainer.style.backgroundColor = "#e8f8f5";
          this.feedbackContainer.style.color = "#27ae60";
          this.feedbackContainer.style.border = "1px solid #a3e4d7";
          this.cityInput.value = "";
        } else {
          throw new Error(result.error);
        }
      } catch (error) {
        this.feedbackContainer.textContent = error.message;
        this.feedbackContainer.style.backgroundColor = "#fdedec";
        this.feedbackContainer.style.color = "#c0392b";
        this.feedbackContainer.style.border = "1px solid #f5b7b1";
      }
    });
  }

  async geocodeCity(city) {
    const query = encodeURIComponent(`${city}, France`);
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=${query}`;

    try {
      const response = await fetch(url, {
        headers: {
          "Accept-Language": "fr",
        },
      });

      if (!response.ok) {
        return null;
      }

      const data = await response.json();
      if (!Array.isArray(data) || data.length === 0) {
        return null;
      }

      const location = data[0];
      return {
        lat: parseFloat(location.lat),
        lng: parseFloat(location.lon),
      };
    } catch (error) {
      console.error("Erreur géocodage profil :", error);
      return null;
    }
  }
}
