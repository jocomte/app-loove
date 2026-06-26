// frontend/src/controllers/DashboardController.js

import { getApiUrl, fetchJson } from "../config.js";
import { MatchModel } from "../models/MatchModel.js";

export class DashboardController {
  constructor() {
    this.matchModel = new MatchModel();

    // Éléments du DOM
    this.geoBtn = document.getElementById("geo-btn");
    this.geoStatus = document.getElementById("geo-status");
    this.profileCard = document.getElementById("profile-card");
    this.profilePhoto = document.getElementById("profile-photo");
    this.avatarPlaceholder = document.getElementById(
      "profile-avatar-placeholder",
    );
    this.profileName = document.getElementById("profile-name");
    this.profileBio = document.getElementById("profile-bio");
    this.profileDistance = document.getElementById("profile-distance");
    this.noProfilesBlock = document.getElementById("no-profiles");
    this.manualLocationForm = document.getElementById("manual-location-form");
    this.cityInput = document.getElementById("city-input");
    this.locationSetupContainer = document.getElementById("location-setup-container");
    this.toggleLocationBtn = document.getElementById("toggle-location-btn");

    // Boutons d'action
    this.passBtn = document.getElementById("pass-btn");
    this.likeBtn = document.getElementById("like-btn");

    // Éléments du Modal de Match
    this.matchModal = document.getElementById("match-modal");
    this.matchPartnerName = document.getElementById("match-partner-name");
    this.matchAvatarMe = document.getElementById("match-avatar-me");
    this.matchAvatarPartner = document.getElementById("match-avatar-partner");
    this.matchChatBtn = document.getElementById("match-chat-btn");
    this.matchCloseBtn = document.getElementById("match-close-btn");

    // Éléments de filtres et signalements
    this.advancedFiltersLock = document.getElementById("advanced-filters-lock");
    this.advancedFiltersForm = document.getElementById("advanced-filters-form");
    this.reportBtn = document.getElementById("report-btn");
    this.reportModal = document.getElementById("report-modal");
    this.reportForm = document.getElementById("report-form");
    this.reportCancelBtn = document.getElementById("report-cancel-btn");
    this.reportReasonInput = document.getElementById("report-reason");

    this.latitude = null;
    this.longitude = null;
    this.currentProfileId = null;
    this.currentProfilePhotoUrl = "";
    this.currentProfileName = "";
    this.myAvatarUrl = "";
    this.isPremiumUser = false;
    this.activeFilters = {};

    if (this.geoBtn) {
      this.initEvents();
      this.fetchMyAvatar().then(() => this.checkPremiumAndLoad());
    }
  }

  initEvents() {
    // Bouton de géolocalisation
    this.geoBtn.addEventListener("click", () => {
      if (!navigator.geolocation) {
        this.showStatus(
          "La géolocalisation n'est pas supportée par votre navigateur.",
          "error",
        );
        return;
      }

      this.showStatus("Recherche de votre position...", "info");

      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.latitude = position.coords.latitude;
          this.longitude = position.coords.longitude;
          this.showStatus("Position synchronisée avec succès !", "success");
          this.saveLocation()
            .then(() => this.loadProfile())
            .catch(() => this.loadProfile());
        },
        (error) => {
          let message = "Impossible de récupérer votre position.";
          if (error.code === error.PERMISSION_DENIED) {
            message =
              "Accès à la géolocalisation refusé. Utilisez le formulaire manuel ci-dessous.";
          } else if (error.code === error.POSITION_UNAVAILABLE) {
            message = "Position indisponible. Essayez à nouveau plus tard.";
          } else if (error.code === error.TIMEOUT) {
            message = "Le délai de géolocalisation est dépassé. Réessayez.";
          }
          this.showStatus(message, "error");
        },
      );
    });

    // Événement sur le bouton PASSER
    if (this.passBtn) {
      this.passBtn.addEventListener("click", () => this.handleAction("pass"));
    }

    // Événement sur le bouton LIKER
    if (this.likeBtn) {
      this.likeBtn.addEventListener("click", () => this.handleAction("like"));
    }

    // Événement fermeture du Modal de Match
    if (this.matchCloseBtn) {
      this.matchCloseBtn.addEventListener("click", () => {
        this.matchModal.classList.remove("active");
      });
    }

    if (this.manualLocationForm) {
      this.manualLocationForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const city = this.cityInput.value.trim();

        if (!city) {
          this.showStatus("Veuillez saisir le nom de votre ville.", "error");
          return;
        }

        this.showStatus("Recherche de la ville... Patientez svp.", "info");

        const coordinates =
          this.getCityCoordinates(city.toLowerCase()) ||
          (await this.fetchCityCoordinates(city));

        if (coordinates) {
          this.latitude = coordinates.lat;
          this.longitude = coordinates.lng;
          this.showStatus(
            `Position définie sur ${coordinates.name}. Chargement des profils...`,
            "success",
          );
          try {
            await this.saveLocation();
          } catch {
            // On continue même si la sauvegarde a échoué
          }
          this.loadProfile();
        } else {
          this.showStatus(
            "Ville introuvable. Vérifiez l'orthographe ou essayez une autre ville.",
            "error",
          );
        }
      });
    }

    // Filtres avancés
    if (this.advancedFiltersForm) {
      this.advancedFiltersForm.addEventListener("submit", (e) => {
        e.preventDefault();
        this.activeFilters = {
          age_min: document.getElementById("filter-age-min").value,
          age_max: document.getElementById("filter-age-max").value,
          relation: document.getElementById("filter-relation").value,
          keyword: document.getElementById("filter-keyword").value
        };
        this.loadProfile();
      });
    }

    // Bouton de signalement
    if (this.reportBtn) {
      this.reportBtn.addEventListener("click", () => {
        if (this.reportModal) {
          this.reportModal.classList.add("active");
        }
      });
    }

    // Bouton annuler signalement
    if (this.reportCancelBtn) {
      this.reportCancelBtn.addEventListener("click", () => {
        if (this.reportModal) {
          this.reportModal.classList.remove("active");
        }
      });
    }

    // Envoi du signalement
    if (this.reportForm) {
      this.reportForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const reason = this.reportReasonInput.value.trim();
        if (!reason || !this.currentProfileId) return;

        try {
          const res = await fetchJson(`${getApiUrl()}?action=submit-report`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ reported_id: this.currentProfileId, reason: reason }),
            credentials: "include"
          });

          alert(res.message);
          this.reportModal.classList.remove("active");
          this.reportReasonInput.value = "";
          
          // Passer automatiquement
          this.handleAction("pass");
        } catch (error) {
          alert("Erreur de signalement : " + error.message);
        }
      });
    }

    if (this.toggleLocationBtn) {
      this.toggleLocationBtn.addEventListener("click", () => {
        if (this.locationSetupContainer) {
          const isHidden = this.locationSetupContainer.style.display === "none";
          this.locationSetupContainer.style.display = isHidden ? "block" : "none";
          this.toggleLocationBtn.textContent = isHidden ? "❌ Fermer les réglages" : "📍 Modifier ma position";
          this.toggleLocationBtn.style.background = isHidden ? "#fdedec" : "#fafbfc";
          this.toggleLocationBtn.style.color = isHidden ? "#c0392b" : "var(--color-text-muted)";
          this.toggleLocationBtn.style.borderColor = isHidden ? "#f5b7b1" : "var(--color-border)";
        }
      });
    }
  }

  showStatus(message, type = "info") {
    if (!this.geoStatus) return;
    this.geoStatus.textContent = message;
    this.geoStatus.style.color =
      type === "success" ? "green" : type === "error" ? "red" : "#34495e";
  }

  getCityCoordinates(cityKey) {
    const cities = {
      paris: { name: "Paris", lat: 48.8566, lng: 2.3522 },
      lyon: { name: "Lyon", lat: 45.764, lng: 4.8357 },
      marseille: { name: "Marseille", lat: 43.2965, lng: 5.3698 },
      lille: { name: "Lille", lat: 50.6292, lng: 3.0573 },
      bordeaux: { name: "Bordeaux", lat: 44.8378, lng: -0.5792 },
      nantes: { name: "Nantes", lat: 47.2184, lng: -1.5536 },
      strasbourg: { name: "Strasbourg", lat: 48.5734, lng: 7.7521 },
      toulouse: { name: "Toulouse", lat: 43.6047, lng: 1.4442 },
      montpellier: { name: "Montpellier", lat: 43.6108, lng: 3.8767 },
      rennes: { name: "Rennes", lat: 48.1173, lng: -1.6778 },
      grenoble: { name: "Grenoble", lat: 45.1885, lng: 5.7245 },
      dijon: { name: "Dijon", lat: 47.322, lng: 5.0415 },
    };

    return cities[cityKey] || null;
  }

  async fetchCityCoordinates(city) {
    const query = encodeURIComponent(`${city}, France`);
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=${query}`;

    try {
      const response = await fetch(url, {
        headers: {
          "Accept-Language": "fr",
        },
      });

      if (!response.ok) {
        console.warn("Erreur geocoding city:", response.status);
        return null;
      }

      const data = await response.json();
      if (!Array.isArray(data) || data.length === 0) {
        return null;
      }

      const location = data[0];
      return {
        name: location.display_name,
        lat: parseFloat(location.lat),
        lng: parseFloat(location.lon),
      };
    } catch (error) {
      console.error(
        "Impossible de récupérer les coordonnées de la ville:",
        error,
      );
      return null;
    }
  }

  async saveLocation() {
    if (this.latitude === null || this.longitude === null) {
      return;
    }

    try {
      await fetchJson(`${getApiUrl()}?action=update-location`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({
          latitude: this.latitude,
          longitude: this.longitude,
        }),
      });

      this.showStatus("Localisation enregistrée.", "success");
      this.hideLocationUI();
    } catch (error) {
      this.showStatus(error.message, "error");
      throw error;
    }
  }

  async handleAction(type) {
    if (!this.currentProfileId) return;

    // Déclencher l'animation de swiping CSS
    if (this.profileCard) {
      if (type === "like") {
        this.profileCard.classList.add("swipe-right");
      } else {
        this.profileCard.classList.add("swipe-left");
      }
    }

    // On attend la fin de l'animation de transition avant de requêter et recharger le profil
    setTimeout(async () => {
      try {
        const result = await this.matchModel.sendInteraction(
          this.currentProfileId,
          type,
        );

        if (result.isMatch) {
          // Afficher le modal de Match personnalisé
          if (this.matchModal) {
            if (this.matchPartnerName) this.matchPartnerName.textContent = this.currentProfileName;
            if (this.matchAvatarPartner) this.matchAvatarPartner.src = this.currentProfilePhotoUrl || "";
            if (this.matchAvatarMe) this.matchAvatarMe.src = this.myAvatarUrl || "";

            // Lier le clic vers la messagerie avec l'ID du partenaire de match
            const matchTargetId = this.currentProfileId;
            if (this.matchChatBtn) {
              this.matchChatBtn.onclick = () => {
                window.location.href = `./messages.html?target_id=${matchTargetId}`;
              };
            }

            this.matchModal.classList.add("active");
          }
        }

        this.loadProfile();
      } catch (error) {
        console.error("Erreur lors de l'interaction :", error);
        this.showStatus("Erreur serveur, réessayez plus tard.", "error");
        if (this.profileCard) {
          this.profileCard.classList.remove("swipe-left", "swipe-right");
        }
      }
    }, 400);
  }

  async loadProfile() {
    try {
      const profile = await this.matchModel.fetchNextProfile(
        this.latitude,
        this.longitude,
        this.activeFilters
      );

      // Réinitialiser les animations de swiping si la carte existe
      if (this.profileCard) {
        this.profileCard.classList.remove("swipe-left", "swipe-right");
      }

      if (profile) {
        this.currentProfileId = profile.id;
        this.currentProfilePhotoUrl = profile.photo_url || "";
        this.currentProfileName = profile.firstname || "";

        const age =
          new Date().getFullYear() - new Date(profile.birthdate).getFullYear();

        this.profileName.textContent = `${profile.firstname}, ${age} ans`;
        this.profileBio.textContent = profile.bio
          ? profile.bio
          : "Aucune description fournie.";
        this.profileDistance.textContent = profile.distance
          ? `À ${parseFloat(profile.distance).toFixed(1)} km de vous`
          : "Distance inconnue";

        if (this.profilePhoto) {
          if (profile.photo_url) {
            this.profilePhoto.src = profile.photo_url;
            this.profilePhoto.style.display = "block";
            if (this.avatarPlaceholder) {
              this.avatarPlaceholder.style.display = "none";
            }
          } else {
            this.profilePhoto.style.display = "none";
            if (this.avatarPlaceholder) {
              this.avatarPlaceholder.style.display = "flex";
            }
          }
        }

        this.noProfilesBlock.style.display = "none";
        this.profileCard.style.display = "block";

        // Enregistrer la visite en arrière-plan
        this.logVisit(profile.id);
      } else {
        this.currentProfileId = null;
        this.currentProfilePhotoUrl = "";
        this.currentProfileName = "";
        this.profileCard.style.display = "none";
        this.noProfilesBlock.style.display = "block";
      }
    } catch (error) {
      console.error(error);
      this.showStatus("Erreur lors du chargement des profils.", "error");
    }
  }

  async fetchMyAvatar() {
    try {
      const response = await fetchJson(`${getApiUrl()}?action=user-photos`, {
        method: "GET",
        credentials: "include",
      });
      if (response && response.photos) {
        const mainPhoto = response.photos.find((p) => p.is_main == 1);
        this.myAvatarUrl = mainPhoto ? mainPhoto.url : "";
      }
    } catch (error) {
      console.warn("Impossible de récupérer ma photo de profil:", error);
    }
  }

  async checkPremiumAndLoad() {
    try {
      const result = await fetchJson(`${getApiUrl()}?action=get-profile`, {
        method: "GET",
        credentials: "include"
      });
      if (result.user) {
        this.isPremiumUser = result.user.is_premium == 1;
        if (this.advancedFiltersLock) {
          this.advancedFiltersLock.style.display = this.isPremiumUser ? "none" : "flex";
        }
        if (result.user.latitude && result.user.longitude) {
          this.latitude = parseFloat(result.user.latitude);
          this.longitude = parseFloat(result.user.longitude);
          this.hideLocationUI();
        }
      }
    } catch (error) {
      console.warn("Impossible de vérifier le statut premium:", error);
    }
    this.loadProfile();
  }

  async logVisit(visitedId) {
    try {
      await fetchJson(`${getApiUrl()}?action=log-visit`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ visited_id: visitedId }),
        credentials: "include"
      });
    } catch (error) {
      console.warn("Impossible de consigner la visite :", error);
    }
  }

  hideLocationUI() {
    if (this.locationSetupContainer) {
      this.locationSetupContainer.style.display = "none";
    }
    if (this.toggleLocationBtn) {
      this.toggleLocationBtn.style.display = "inline-block";
      this.toggleLocationBtn.textContent = "📍 Modifier ma position";
      this.toggleLocationBtn.style.background = "#fafbfc";
      this.toggleLocationBtn.style.color = "var(--color-text-muted)";
      this.toggleLocationBtn.style.borderColor = "var(--color-border)";
    }
  }
}
