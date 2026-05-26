// frontend/src/models/AuthModel.js

export class AuthModel {
  constructor() {
    this.apiUrl = "http://localhost/app-loove/backend/index.php";
  }

  async registerUser(userData) {
    try {
      const response = await fetch(`${this.apiUrl}?action=register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(userData),
      });

      const result = await response.json();

      // response.ok est vrai pour tous les statuts entre 200 et 299
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
      const response = await fetch(`${this.apiUrl}?action=login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(credentials),
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.error || "Identifiants incorrects.");
      }

      return result;
    } catch (error) {
      throw error;
    }
  }
}
