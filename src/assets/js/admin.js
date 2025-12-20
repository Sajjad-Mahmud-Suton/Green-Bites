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
 * ║  FILE: admin.js                                                           ║
 * ║  PATH: /js/admin.js                                                       ║
 * ║  DESCRIPTION: Admin panel functionality for Green Bites (Firebase)        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  SECTIONS:                                                                ║
 * ║    1. Imports & Firebase Dependencies                                     ║
 * ║    2. Menu Management (CRUD)                                              ║
 * ║    3. Table Management                                                    ║
 * ║    4. Order Management (Real-time)                                        ║
 * ║    5. Complaint Management (Real-time)                                    ║
 * ║    6. Initialization                                                      ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  DEPENDENCIES:                                                            ║
 * ║    - firebase-config.js (db)                                              ║
 * ║    - Firebase SDK v10.14.1                                                ║
 * ║    - Bootstrap 5.x                                                        ║
 * ╠═══════════════════════════════════════════════════════════════════════════╣
 * ║  (c) 2024 Green Bites - University Canteen Management System              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 1: IMPORTS & FIREBASE DEPENDENCIES
   ═══════════════════════════════════════════════════════════════════════════ */

import { db } from "./firebase-config.js";
import {
  collection, getDocs, addDoc, updateDoc, doc,
  onSnapshot, serverTimestamp, deleteDoc
} from "https://www.gstatic.com/firebasejs/10.14.1/firebase-firestore.js";


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 2: MENU MANAGEMENT (CRUD)
   ═══════════════════════════════════════════════════════════════════════════ */

// Menu form DOM elements
const menuForm = document.getElementById("menuForm");
const menuId = document.getElementById("menuId");
const menuName = document.getElementById("menuName");
const menuPrice = document.getElementById("menuPrice");
const menuDesc = document.getElementById("menuDesc");
const menuSpecial = document.getElementById("menuSpecial");
const menuList = document.getElementById("menuList");
const menuReset = document.getElementById("menuReset");

/**
 * Load all menu items for admin management
 * Displays edit/delete buttons for each item
 */
async function loadMenuAdmin() {
  const snap = await getDocs(collection(db, "menu"));
  menuList.innerHTML = "";
  snap.forEach(docSnap => {
    const item = docSnap.data();
    const li = document.createElement("li");
    li.className = "list-group-item d-flex justify-content-between align-items-center";
    li.innerHTML = `
      <div>
        <div class="fw-semibold">${item.name} - ৳${item.price}</div>
        <div class="small text-muted">${item.description || ""}</div>
        ${item.isSpecial ? '<span class="badge bg-warning text-dark">Special</span>' : ""}
      </div>
      <div>
        <button class="btn btn-sm btn-outline-primary me-1 edit-btn" data-id="${docSnap.id}" title="Edit">
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${docSnap.id}" title="Delete">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    `;
    menuList.appendChild(li);
  });

  // Edit button event
  menuList.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", () => editMenuItem(btn.dataset.id));
  });
  // Delete button event
  menuList.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", () => deleteMenuItem(btn.dataset.id));
  });
}

async function editMenuItem(id) {
  const snap = await getDocs(collection(db, "menu"));
  snap.forEach(docSnap => {
    if (docSnap.id === id) {
      const item = docSnap.data();
      menuId.value = id;
      menuName.value = item.name;
      menuPrice.value = item.price;
      menuDesc.value = item.description || "";
      menuSpecial.checked = item.isSpecial === true;
    }
  });
}

/**
 * Menu form submission handler
 * Creates new or updates existing menu item
 */
menuForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const data = {
    name: menuName.value.trim(),
    price: Number(menuPrice.value),
    description: menuDesc.value.trim(),
    isSpecial: menuSpecial.checked,
    updatedAt: serverTimestamp()
  };
  if (menuId.value) {
    await updateDoc(doc(db, "menu", menuId.value), data);
  } else {
    data.createdAt = serverTimestamp();
    await addDoc(collection(db, "menu"), data);
  }
  menuForm.reset();
  menuId.value = "";
  loadMenuAdmin();
});

menuReset.addEventListener("click", () => {
  menuForm.reset();
  menuId.value = "";
});

/**
 * Delete a menu item after confirmation
 * @param {string} id - Firestore document ID
 */
async function deleteMenuItem(id) {
  if (confirm("Are you sure you want to delete this menu item?")) {
    await deleteDoc(doc(db, "menu", id));
    loadMenuAdmin();
  }
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 3: TABLE MANAGEMENT
   ═══════════════════════════════════════════════════════════════════════════ */

const adminTables = document.getElementById("adminTables");

/**
 * Load all tables for admin management
 * Shows toggle button for occupied/free status
 */
async function loadTablesAdmin() {
  const snap = await getDocs(collection(db, "tables"));
  adminTables.innerHTML = "";
  snap.forEach(docSnap => {
    const table = docSnap.data();
    const div = document.createElement("div");
    div.className = "col";
    div.innerHTML = `
      <div class="card text-center py-2 ${table.isOccupied ? "table-occupied" : "table-free"}">
        <div>Table ${table.number}</div>
        <button class="btn btn-sm ${table.isOccupied ? "btn-danger" : "btn-success"} mt-2 toggle-table" data-id="${docSnap.id}">
          ${table.isOccupied ? "Mark Free" : "Mark Occupied"}
        </button>
      </div>
    `;
    adminTables.appendChild(div);
  });
  adminTables.querySelectorAll(".toggle-table").forEach(btn => {
    btn.addEventListener("click", () => toggleTable(btn.dataset.id));
  });
}

/**
 * Toggle table occupied/free status
 * @param {string} id - Firestore document ID
 */
async function toggleTable(id) {
  const snap = await getDocs(collection(db, "tables"));
  snap.forEach(async docSnap => {
    if (docSnap.id === id) {
      const table = docSnap.data();
      await updateDoc(doc(db, "tables", id), {
        isOccupied: !table.isOccupied
      });
      loadTablesAdmin();
    }
  });
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 4: ORDER MANAGEMENT (REAL-TIME)
   ═══════════════════════════════════════════════════════════════════════════ */

const adminOrders = document.getElementById("adminOrders");

/**
 * Listen to real-time order updates
 * Allows admin to change order status
 */
function listenOrdersAdmin() {
  onSnapshot(collection(db, "orders"), snap => {
    adminOrders.innerHTML = "";
    snap.forEach(docSnap => {
      const order = docSnap.data();
      const div = document.createElement("div");
      div.className = "list-group-item";
      div.innerHTML = `
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-semibold">Order ${docSnap.id}</div>
            <div class="small text-muted">User: ${order.userId || "N/A"} | Table ${order.tableNumber}</div>
          </div>
          <div>
            <select class="form-select form-select-sm order-status" data-id="${docSnap.id}">
              ${["pending","preparing","ready","completed"].map(s =>
                `<option value="${s}" ${order.status === s ? "selected" : ""}>${s}</option>`
              ).join("")}
            </select>
          </div>
        </div>
      `;
      adminOrders.appendChild(div);
    });
    adminOrders.querySelectorAll(".order-status").forEach(sel => {
      sel.addEventListener("change", () => updateOrderStatus(sel.dataset.id, sel.value));
    });
  });
}

/**
 * Update order status in Firestore
 * @param {string} id - Order document ID
 * @param {string} status - New status value
 */
async function updateOrderStatus(id, status) {
  await updateDoc(doc(db, "orders", id), { status });
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 5: COMPLAINT MANAGEMENT (REAL-TIME)
   ═══════════════════════════════════════════════════════════════════════════ */

const adminComplaints = document.getElementById("adminComplaints");

/**
 * Listen to real-time complaint updates
 * Displays all customer complaints with details
 */
function listenComplaintsAdmin() {
  onSnapshot(collection(db, "complaints"), snap => {
    adminComplaints.innerHTML = "";
    snap.forEach(docSnap => {
      const c = docSnap.data();
      const div = document.createElement("div");
      div.className = "list-group-item";
      div.innerHTML = `
        <div class="fw-semibold">Complaint ${docSnap.id}</div>
        <div class="small">Order: ${c.orderId || "N/A"} | User: ${c.userId || "N/A"}</div>
        <p class="small mb-1">${c.text}</p>
        ${c.imageUrl ? `<a href="${c.imageUrl}" target="_blank">View image</a>` : ""}
      `;
      adminComplaints.appendChild(div);
    });
  });
}


/* ═══════════════════════════════════════════════════════════════════════════
   SECTION 6: INITIALIZATION
   ═══════════════════════════════════════════════════════════════════════════ */

// Initialize all admin functions on page load
loadMenuAdmin();
loadTablesAdmin();
listenOrdersAdmin();
listenComplaintsAdmin();


/* ═══════════════════════════════════════════════════════════════════════════
   END OF FILE
   ═══════════════════════════════════════════════════════════════════════════ */