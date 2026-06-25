// frontend/src/models/PhotoModel.js
import { getApiUrl, fetchJson } from "../config.js";

export class PhotoModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  async uploadPhoto(formData) {
    const result = await fetchJson(`${this.apiUrl}?action=upload-photo`, {
      method: "POST",
      body: formData,
      credentials: "include",
    });

    return result;
  }

  async fetchPhotos() {
    const result = await fetchJson(`${this.apiUrl}?action=user-photos`, {
      method: "GET",
      credentials: "include",
    });

    return result.photos;
  }

  async deletePhoto(photoId) {
    const result = await fetchJson(`${this.apiUrl}?action=delete-photo`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ photo_id: photoId }),
      credentials: "include",
    });

    return result;
  }

  async setMainPhoto(photoId) {
    const result = await fetchJson(`${this.apiUrl}?action=set-main-photo`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ photo_id: photoId }),
      credentials: "include",
    });

    return result;
  }
}
