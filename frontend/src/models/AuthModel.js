// frontend/src/models/AuthModel.js
import { getApiUrl, fetchWithTimeout } from "../config.js";

export class AuthModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  async registerUser(userData) {
    try {
      const response = await fetchWithTimeout(
        `${this.apiUrl}?action=register`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(userData),
          credentials: "include", // 🚀 Transmet les cookies de session pour le smartphone
        },
        15000,
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.error || "Une erreur est survenue.");
      }

      return result;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Envoie les données de connexion au serveur
   */
  async loginUser(credentials) {
    try {
      const response = await fetchWithTimeout(
        `${this.apiUrl}?action=login`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(credentials),
          credentials: "include", // 🚀 Essentiel pour initialiser le PHPSESSID
        },
        15000,
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.error || "Identifiants incorrects.");
      }

      return result;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Envoie le code de vérification au serveur
   */
  async verifyEmail(email, code) {
    try {
      const response = await fetchWithTimeout(
        `${this.apiUrl}?action=verify_email`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ email, code }),
          credentials: "include",
        },
        15000,
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.error || "Code invalide.");
      }

      return result;
    } catch (error) {
      throw error;
    }
  }
}

