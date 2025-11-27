import { db } from "./firebase-config.js";
import {
  collection, getDocs, addDoc, updateDoc, doc,
  onSnapshot, serverTimestamp, deleteDoc
} from "https://www.gstatic.com/firebasejs/10.14.1/firebase-firestore.js";

const menuForm = document.getElementById("menuForm");
const menuId = document.getElementById("menuId");
const menuName = document.getElementById("menuName");
const menuPrice = document.getElementById("menuPrice");
const menuDesc = document.getElementById("menuDesc");
const menuSpecial = document.getElementById("menuSpecial");
const menuList = document.getElementById("menuList");
const menuReset = document.getElementById("menuReset");

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

async function deleteMenuItem(id) {
  if (confirm("Are you sure you want to delete this menu item?")) {
    await deleteDoc(doc(db, "menu", id));
    loadMenuAdmin();
  }
}

const adminTables = document.getElementById("adminTables");

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

const adminOrders = document.getElementById("adminOrders");

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

async function updateOrderStatus(id, status) {
  await updateDoc(doc(db, "orders", id), { status });
}

const adminComplaints = document.getElementById("adminComplaints");

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

// Load everything (on page load)
loadMenuAdmin();
loadTablesAdmin();
listenOrdersAdmin();
listenComplaintsAdmin();