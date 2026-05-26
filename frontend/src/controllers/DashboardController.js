// frontend/src/controllers/DashboardController.js

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

    this.latitude = null;
    this.longitude = null;

    if (this.geoBtn) {
      this.initEvents();
      // On charge un premier profil au démarrage avec les coordonnées par défaut
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
          // Recharge le profil avec la vraie position GPS
          this.loadProfile();
        },
        () => {
          this.geoStatus.textContent =
            "Impossible de récupérer votre position (accès refusé).";
          this.geoStatus.style.color = "red";
        },
      );
    });
  }

  async loadProfile() {
    try {
      const profile = await this.matchModel.fetchNextProfile(
        this.latitude,
        this.longitude,
      );

      if (profile) {
        // Calcule l'âge à partir de la date de naissance
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
        this.profileCard.style.display = "none";
        this.noProfilesBlock.style.display = "block";
      }
    } catch (error) {
      console.error(error);
    }
  }
}
