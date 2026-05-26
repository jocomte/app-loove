// frontend/src/app.js

import { AuthController } from "./controllers/AuthController.js";
import { DashboardController } from "./controllers/DashboardController.js"; // <-- Ajouté !

document.addEventListener("DOMContentLoaded", () => {
  console.log("Application lancée en mode POO/MVC !");
  new AuthController();
  new DashboardController(); // <-- Initialisé !
});
