// frontend/src/models/PhotoModel.js
import { getApiUrl, fetchWithTimeout } from "../config.js";

export class PhotoModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  async uploadPhoto(formData) {
    const response = await fetchWithTimeout(
      `${this.apiUrl}?action=upload-photo`,
      {
        method: "POST",
        body: formData,
        credentials: "include",
      },
    );

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.error || "Erreur lors de l'upload de la photo.");
    }
    return result;
  }

  async fetchPhotos() {
    const response = await fetchWithTimeout(
      `${this.apiUrl}?action=user-photos`,
      {
        method: "GET",
        credentials: "include",
      },
    );

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.error || "Impossible de charger les photos.");
    }
    return result.photos;
  }
}
