// frontend/src/controllers/DashboardController.js

import { getApiUrl, fetchWithTimeout } from "../config.js";
import { MatchModel } from "../models/MatchModel.js";

export class DashboardController {
  constructor() {
    this.matchModel = new MatchModel();

    // Éléments du DOM
    this.geoBtn = document.getElementById("geo-btn");
    this.geoStatus = document.getElementById("geo-status");
    this.profileCard = document.getElementById("profile-card");
    this.profileName = document.getElementById("profile-name");
    this.profileBio = document.getElementById("profile-bio");
    this.profileDistance = document.getElementById("profile-distance");
    this.noProfilesBlock = document.getElementById("no-profiles");
    this.manualLocationForm = document.getElementById("manual-location-form");
    this.cityInput = document.getElementById("city-input");

    // 🚀 Boutons d'action ajoutés
    this.passBtn = document.getElementById("pass-btn");
    this.likeBtn = document.getElementById("like-btn");

    this.latitude = null;
    this.longitude = null;
    this.currentProfileId = null; // 🚀 Stocke l'ID du profil actuellement affiché

    if (this.geoBtn) {
      this.initEvents();
      this.loadProfile();
    }
  }

  initEvents() {
    // Bouton de géolocalisation
    this.geoBtn.addEventListener("click", () => {
      if (!navigator.geolocation) {
        this.geoStatus.textContent =
          "La géolocalisation n'est pas supportée par votre navigateur.";
        return;
      }

      this.geoStatus.textContent = "Recherche de votre position...";

      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.latitude = position.coords.latitude;
          this.longitude = position.coords.longitude;
          this.geoStatus.textContent = "Position synchronisée avec succès !";
          this.geoStatus.style.color = "green";
          this.saveLocation().finally(() => this.loadProfile());
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
          this.geoStatus.textContent = message;
          this.geoStatus.style.color = "red";
        },
      );
    });

    // 🚀 Événement sur le bouton PASSER
    if (this.passBtn) {
      this.passBtn.addEventListener("click", () => this.handleAction("pass"));
    }

    // 🚀 Événement sur le bouton LIKER
    if (this.likeBtn) {
      this.likeBtn.addEventListener("click", () => this.handleAction("like"));
    }

    if (this.manualLocationForm) {
      this.manualLocationForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const city = this.cityInput.value.trim();

        if (!city) {
          this.geoStatus.textContent = "Veuillez saisir le nom de votre ville.";
          this.geoStatus.style.color = "red";
          return;
        }

        this.geoStatus.textContent = "Recherche de la ville... Patientez svp.";
        this.geoStatus.style.color = "black";

        const coordinates =
          this.getCityCoordinates(city.toLowerCase()) ||
          (await this.fetchCityCoordinates(city));

        if (coordinates) {
          this.latitude = coordinates.lat;
          this.longitude = coordinates.lng;
          this.geoStatus.textContent = `Position définie sur ${coordinates.name}. Chargement des profils...`;
          this.geoStatus.style.color = "green";
          this.saveLocation().finally(() => this.loadProfile());
        } else {
          this.geoStatus.textContent =
            "Ville introuvable. Vérifiez l'orthographe ou essayez une autre ville.";
          this.geoStatus.style.color = "red";
        }
      });
    }
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

    const apiUrl = getApiUrl();
    try {
      const response = await fetchWithTimeout(
        apiUrl + "?action=update-location",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({
            latitude: this.latitude,
            longitude: this.longitude,
          }),
        },
      );

      const result = await response.json();
      if (!response.ok) {
        console.warn("Impossible de sauvegarder la position :", result.error);
      }
    } catch (error) {
      console.warn("Erreur lors de la sauvegarde de la position :", error);
    }
  }

  // 🚀 Envoi de l'action au serveur et chargement du profil suivant
  async handleAction(type) {
    if (!this.currentProfileId) return;

    try {
      // Appel de la méthode créée précédemment dans MatchModel
      const result = await this.matchModel.sendInteraction(
        this.currentProfileId,
        type,
      );

      if (result.isMatch) {
        alert("🎉 C'est un MATCH ! Vous pouvez maintenant discuter !");
      }

      // On passe immédiatement au profil suivant
      this.loadProfile();
    } catch (error) {
      console.error("Erreur lors de l'interaction :", error);
    }
  }

  async loadProfile() {
    try {
      const profile = await this.matchModel.fetchNextProfile(
        this.latitude,
        this.longitude,
      );

      if (profile) {
        this.currentProfileId = profile.id; // 🚀 On mémorise l'ID du profil reçu

        const age =
          new Date().getFullYear() - new Date(profile.birthdate).getFullYear();

        this.profileName.textContent = `${profile.firstname}, ${age} ans`;
        this.profileBio.textContent = profile.bio
          ? profile.bio
          : "Aucune description fournie.";
        this.profileDistance.textContent = `À ${parseFloat(profile.distance).toFixed(1)} km de vous`;

        this.noProfilesBlock.style.display = "none";
        this.profileCard.style.display = "block";
      } else {
        this.currentProfileId = null;
        this.profileCard.style.display = "none";
        this.noProfilesBlock.style.display = "block";
      }
    } catch (error) {
      console.error(error);
    }
  }
}
