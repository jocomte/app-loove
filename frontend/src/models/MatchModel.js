// frontend/src/models/MatchModel.js
import { getApiUrl, fetchWithTimeout } from "../config.js";

export class MatchModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  /**
   * Va chercher le prochain profil compatible via l'API
   */
  async fetchNextProfile(lat = null, lng = null) {
    try {
      let url = `${this.apiUrl}?action=next-profile`;
      if (lat && lng) {
        url += `&lat=${lat}&lng=${lng}`;
      }

      const response = await fetchWithTimeout(
        url,
        {
          method: "GET",
          credentials: "include", // 🚀 Maintient l'état connecté lors de la récupération
        },
        15000,
      );
      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.error || "Erreur lors du chargement du profil.");
      }

      return result.profile;
    } catch (error) {
      throw error;
    }
  }
}
