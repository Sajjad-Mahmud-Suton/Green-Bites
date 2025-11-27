// /js/firebase-config.js

// Firebase er main app import
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js";

// Firestore (database) import
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-firestore.js";

// Storage (image upload) import
import { getStorage } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-storage.js";

// OPTIONAL: Analytics dorkar na hole bad dite paren
// import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-analytics.js";

// Ei part ta EXACT আপনার deya config theke copy
const firebaseConfig = {
  apiKey: "AIzaSyDf1wo_Ojfz1l3kwaTt0x9KOSyNQnD_5HE",
  authDomain: "green-bites-1046e.firebaseapp.com",
  projectId: "green-bites-1046e",
  storageBucket: "green-bites-1046e.firebasestorage.app",
  messagingSenderId: "42975660314",
  appId: "1:42975660314:web:5e4d6dcbe86c6520bdc4a3",
  measurementId: "G-D04FXRESXC"
};

// Firebase initialize
const app = initializeApp(firebaseConfig);

// Analytics dorkar hole
// const analytics = getAnalytics(app);

// Ei duita export korbo, onno file (user.js, admin.js) theke use korar jonno
export const db = getFirestore(app);
export const storage = getStorage(app);
