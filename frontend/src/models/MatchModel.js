// frontend/src/models/MatchModel.js

export class MatchModel {
  constructor() {
    this.apiUrl = "http://localhost/app-loove/backend/index.php";
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

      const response = await fetch(url, { method: "GET" });
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
