// frontend/src/models/MessageModel.js
import { getApiUrl, fetchJson } from "../config.js";

export class MessageModel {
  constructor() {
    this.apiUrl = getApiUrl();
  }

  async sendMessage(receiverId, content) {
    const result = await fetchJson(`${this.apiUrl}?action=send-message`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ receiver_id: receiverId, content }),
    });

    return result;
  }

  async fetchInbox() {
    const result = await fetchJson(`${this.apiUrl}?action=inbox`, {
      method: "GET",
      credentials: "include",
    });

    return result.conversations;
  }

  async fetchConversation(targetId) {
    const result = await fetchJson(
      `${this.apiUrl}?action=conversation&target_id=${targetId}`,
      {
        method: "GET",
        credentials: "include",
      },
    );

    return result.messages;
  }
}
