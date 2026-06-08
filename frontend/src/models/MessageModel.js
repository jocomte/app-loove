// frontend/src/models/MessageModel.js
import { getApiUrl, fetchWithTimeout } from "../config.js";

export class MessageModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  async sendMessage(receiverId, content) {
    const response = await fetchWithTimeout(
      `${this.apiUrl}?action=send-message`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ receiver_id: receiverId, content }),
      },
    );

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.error || "Impossible d'envoyer le message.");
    }
    return result;
  }

  async fetchInbox() {
    const response = await fetchWithTimeout(`${this.apiUrl}?action=inbox`, {
      method: "GET",
      credentials: "include",
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(
        result.error || "Impossible de charger la boîte de réception.",
      );
    }
    return result.conversations;
  }

  async fetchConversation(targetId) {
    const response = await fetchWithTimeout(
      `${this.apiUrl}?action=conversation&target_id=${targetId}`,
      {
        method: "GET",
        credentials: "include",
      },
    );

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.error || "Impossible de charger la conversation.");
    }
    return result.messages;
  }
}
