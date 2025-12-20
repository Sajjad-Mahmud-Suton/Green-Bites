/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                                                                           ║
 * ║   ██████╗ ██████╗ ███████╗███████╗███╗   ██╗    ██████╗ ██╗████████╗███████╗║
 * ║  ██╔════╝ ██╔══██╗██╔════╝██╔════╝████╗  ██║    ██╔══██╗██║╚══██╔══╝██╔════╝║
 * ║  ██║  ███╗██████╔╝█████╗  █████╗  ██╔██╗ ██║    ██████╔╝██║   ██║   █████╗  ║
 * ║  ██║   ██║██╔══██╗██╔══╝  ██╔══╝  ██║╚██╗██║    ██╔══██╗██║   ██║   ██╔══╝  ║
 * ║  ╚██████╔╝██║  ██║███████╗███████╗██║ ╚████║    ██████╔╝██║   ██║   ███████╗║
 * ║   ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝  ╚═══╝    ╚═════╝ ╚═╝   ╚═╝   ╚══════╝║
 * ║                                                                           ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  FILE: firebase-config.js                                                 ║
 * ║  PATH: /js/firebase-config.js                                             ║
 * ║  DESCRIPTION: Firebase configuration and initialization                   ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Firebase SDK Imports                                                ║
 * ║    2. Firebase Configuration Object                                       ║
 * ║    3. Firebase Initialization                                             ║
 * ║    4. Exports (db, storage)                                               ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  DEPENDENCIES:                                                            ║
 * ║    - Firebase SDK v10.14.1                                                ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */

/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: FIREBASE SDK IMPORTS
   ═══════════════════════════════════════════════════════════════════════════ */

// Firebase main app import
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js";

// Firestore (database) import
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-firestore.js";

// Storage (image upload) import
import { getStorage } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-storage.js";

// OPTIONAL: Analytics (uncomment if needed)
// import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-analytics.js";


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: FIREBASE CONFIGURATION OBJECT
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Firebase project configuration
 * NOTE: In production, consider using environment variables
 */
const firebaseConfig = {
  apiKey: "AIzaSyDf1wo_Ojfz1l3kwaTt0x9KOSyNQnD_5HE",
  authDomain: "green-bites-1046e.firebaseapp.com",
  projectId: "green-bites-1046e",
  storageBucket: "green-bites-1046e.firebasestorage.app",
  messagingSenderId: "42975660314",
  appId: "1:42975660314:web:5e4d6dcbe86c6520bdc4a3",
  measurementId: "G-D04FXRESXC"
};


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: FIREBASE INITIALIZATION
   ═══════════════════════════════════════════════════════════════════════════ */

// Initialize Firebase App
const app = initializeApp(firebaseConfig);

// Initialize Analytics (uncomment if needed)
// const analytics = getAnalytics(app);


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: EXPORTS
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Export Firestore database instance
 * Used in: user.js, admin.js for database operations
 */
export const db = getFirestore(app);

/**
 * Export Firebase Storage instance
 * Used for: Image uploads (complaints, menu items)
 */
export const storage = getStorage(app);
