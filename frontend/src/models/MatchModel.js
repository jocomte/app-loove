// frontend/src/models/MatchModel.js
import { getApiUrl, fetchJson } from "../config.js";

export class MatchModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  /**
   * Va chercher le prochain profil compatible via l'API
   */
  async fetchNextProfile(lat = null, lng = null, filters = {}) {
    try {
      let url = `${this.apiUrl}?action=next-profile`;
      if (lat && lng) {
        url += `&lat=${lat}&lng=${lng}`;
      }
      if (filters.age_min) url += `&age_min=${filters.age_min}`;
      if (filters.age_max) url += `&age_max=${filters.age_max}`;
      if (filters.relation) url += `&relation=${filters.relation}`;
      if (filters.keyword) url += `&keyword=${encodeURIComponent(filters.keyword)}`;

      const result = await fetchJson(
        url,
        {
          method: "GET",
          credentials: "include", // 🚀 Maintient l'état connecté lors de la récupération
        },
        15000,
      );

      return result.profile;
    } catch (error) {
      throw error;
    }
  }

  async sendInteraction(targetId, type) {
    try {
      const result = await fetchJson(
        `${this.apiUrl}?action=interaction`,
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ target_id: targetId, type }),
        },
        15000,
      );

      return result;
    } catch (error) {
      throw error;
    }
  }
}
