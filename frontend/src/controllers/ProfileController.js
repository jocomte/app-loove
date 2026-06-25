// frontend/src/controllers/ProfileController.js
import { getApiUrl, fetchJson } from "../config.js";
import { PhotoModel } from "../models/PhotoModel.js";

export class ProfileController {
  constructor() {
    this.apiUrl = getApiUrl();
    this.photoModel = new PhotoModel();
    this.form = document.getElementById("profile-form");
    this.tinderGrid = document.getElementById("tinder-photo-grid");
    this.tinderInput = document.getElementById("tinder-photo-input");
    this.feedbackContainer = document.getElementById("form-feedback");
    this.photoFeedback = document.getElementById("photo-feedback");
    this.premiumBadge = document.getElementById("premium-badge");
    this.cityInput = document.getElementById("city");

    this.adminLinkContainer = document.getElementById("admin-link-container");
    this.visitorsList = document.getElementById("visitors-list");
    this.premiumVisitorsCta = document.getElementById("premium-visitors-cta");

    if (this.form) {
      this.loadUserData();
      this.loadPhotos();
      this.loadVisitors();
      this.initEvents();
    }
  }

  /**
   * Récupère les données de la session actuelle pour pré-remplir le formulaire
   */
  async loadUserData() {
    try {
      const result = await fetchJson(`${this.apiUrl}?action=get-profile`, {
        method: "GET",
        credentials: "include",
      }, 15000);

      if (result.user) {
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

        if (result.user.is_premium == 1 && this.premiumBadge) {
          this.premiumBadge.style.display = "inline-block";
        }

        if (result.user.is_admin == 1 && this.adminLinkContainer) {
          this.adminLinkContainer.style.display = "block";
        }
      } else {
        window.location.href = "./login.html";
      }
    } catch (error) {
      console.error("Erreur de récupération utilisateur:", error);
      window.location.href = "./login.html";
    }
  }

  async loadPhotos() {
    if (!this.tinderGrid) return;

    try {
      const photos = await this.photoModel.fetchPhotos();
      this.tinderGrid.innerHTML = "";

      // Tinder gère typiquement 6 slots de photos
      for (let i = 0; i < 6; i++) {
        const slot = document.createElement("div");
        const photo = photos[i];

        if (photo) {
          slot.className = "tinder-slot filled";

          // Image
          const img = document.createElement("img");
          img.src = photo.url;
          img.alt = `Photo ${i + 1}`;
          slot.appendChild(img);

          // Badge principal ou bouton définir comme principal
          if (photo.is_main == 1) {
            const badge = document.createElement("span");
            badge.className = "slot-badge-main";
            badge.textContent = "★ Principale";
            slot.appendChild(badge);
          } else {
            const mainBtn = document.createElement("button");
            mainBtn.type = "button";
            mainBtn.className = "slot-main-btn";
            mainBtn.innerHTML = "★";
            mainBtn.title = "Définir comme principale";
            mainBtn.addEventListener("click", async (e) => {
              e.preventDefault();
              e.stopPropagation();
              try {
                await this.photoModel.setMainPhoto(photo.id);
                this.loadPhotos();
              } catch (err) {
                alert(err.message);
              }
            });
            slot.appendChild(mainBtn);
          }

          // Bouton de suppression
          const deleteBtn = document.createElement("button");
          deleteBtn.type = "button";
          deleteBtn.className = "slot-delete-btn";
          deleteBtn.innerHTML = "×";
          deleteBtn.title = "Supprimer cette photo";
          deleteBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (confirm("Voulez-vous vraiment supprimer cette photo ?")) {
              try {
                await this.photoModel.deletePhoto(photo.id);
                this.loadPhotos();
              } catch (err) {
                alert(err.message);
              }
            }
          });
          slot.appendChild(deleteBtn);
        } else {
          // Slot vide avec icône "+"
          slot.className = "tinder-slot empty";
          slot.innerHTML = `<span class="slot-add-btn">+</span>`;
          slot.title = "Ajouter une photo";
          
          slot.addEventListener("click", () => {
            if (this.tinderInput) {
              this.tinderInput.click();
            }
          });
        }

        this.tinderGrid.appendChild(slot);
      }
    } catch (error) {
      console.error("Erreur chargement photos Tinder:", error);
      this.tinderGrid.innerHTML = "<p>Impossible de charger vos photos.</p>";
    }
  }

  initEvents() {
    if (this.tinderInput) {
      this.tinderInput.addEventListener("change", async () => {
        if (this.tinderInput.files.length === 0) return;
        const file = this.tinderInput.files[0];
        const formData = new FormData();
        formData.append("photo", file);

        try {
          if (this.photoFeedback) {
            this.photoFeedback.textContent = "Téléversement en cours...";
            this.photoFeedback.style.display = "block";
            this.photoFeedback.style.color = "#2980b9";
            this.photoFeedback.style.backgroundColor = "#ebf5fb";
            this.photoFeedback.style.border = "1px solid #aed6f1";
          }

          const result = await this.photoModel.uploadPhoto(formData);
          
          if (this.photoFeedback) {
            this.photoFeedback.textContent = result.message;
            this.photoFeedback.style.color = "#27ae60";
            this.photoFeedback.style.backgroundColor = "#e8f8f5";
            this.photoFeedback.style.border = "1px solid #a3e4d7";
          }
          
          this.tinderInput.value = "";
          this.loadPhotos();
        } catch (error) {
          if (this.photoFeedback) {
            this.photoFeedback.textContent = error.message;
            this.photoFeedback.style.color = "#c0392b";
            this.photoFeedback.style.backgroundColor = "#fdedec";
            this.photoFeedback.style.border = "1px solid #f5b7b1";
          }
          this.tinderInput.value = "";
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
        const result = await fetchJson(`${this.apiUrl}?action=update-profile`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
          credentials: "include",
        }, 15000);

        this.feedbackContainer.textContent = result.message;
        this.feedbackContainer.style.backgroundColor = "#e8f8f5";
        this.feedbackContainer.style.color = "#27ae60";
        this.feedbackContainer.style.border = "1px solid #a3e4d7";
        this.cityInput.value = "";
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

  async loadVisitors() {
    if (!this.visitorsList) return;

    try {
      const res = await fetchJson(`${this.apiUrl}?action=get-visits`, {
        method: "GET",
        credentials: "include"
      });

      this.visitorsList.innerHTML = "";

      if (res.visits && res.visits.length > 0) {
        res.visits.forEach((v) => {
          const item = document.createElement("div");
          item.style.display = "flex";
          item.style.alignItems = "center";
          item.style.gap = "15px";
          item.style.padding = "10px 15px";
          item.style.background = "#ffffff";
          item.style.borderRadius = "12px";
          item.style.border = "1px solid var(--color-border)";
          
          const avatarUrl = v.visitor_photo || "";
          const avatarHTML = avatarUrl 
            ? `<img src="${avatarUrl}" alt="Avatar" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">`
            : `<div style="width: 45px; height: 45px; border-radius: 50%; display:flex; align-items:center; justify-content:center; background: var(--gradient-primary); color:white; font-weight:700; font-size:16px;">${v.firstname[0]}</div>`;

          const dateFormatted = new Date(v.viewed_at).toLocaleDateString("fr-FR", {
            day: "numeric",
            month: "short",
            hour: "2-digit",
            minute: "2-digit"
          });

          item.innerHTML = `
            ${avatarHTML}
            <div style="flex: 1;">
              <span style="font-weight: 700; color: var(--color-text-dark);">${v.firstname} ${v.lastname}</span>
              <p style="font-size: 11px; color: var(--color-text-muted); margin: 0; margin-top: 2px;">Visité le ${dateFormatted}</p>
            </div>
          `;
          
          this.visitorsList.appendChild(item);
        });
      } else {
        this.visitorsList.innerHTML = `<p style="color: var(--color-text-muted); font-size: 13px; margin: 0;">Aucune visite pour le moment.</p>`;
      }

      // Handle locking overlay if not premium
      if (res.is_premium) {
        if (this.premiumVisitorsCta) this.premiumVisitorsCta.style.display = "none";
      } else {
        if (this.premiumVisitorsCta) this.premiumVisitorsCta.style.display = "flex";
      }

    } catch (error) {
      console.error("Error loading visitors:", error);
      this.visitorsList.innerHTML = `<p style="color: red; font-size: 13px;">Impossible de charger les visiteurs.</p>`;
    }
  }
}
