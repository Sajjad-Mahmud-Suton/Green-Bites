import { db, storage } from "./firebase-config.js";
import {
  collection, getDocs, addDoc, onSnapshot,
  query, where, serverTimestamp
} from "https://www.gstatic.com/firebasejs/10.14.1/firebase-firestore.js";
import {
  ref, uploadBytes, getDownloadURL
} from "https://www.gstatic.com/firebasejs/10.14.1/firebase-storage.js";

const currentUserId = "demoUser1";

const menuContainer = document.getElementById("menuContainer");
const specialsContainer = document.getElementById("specialsContainer");

async function loadMenu() {
  const menuSnap = await getDocs(collection(db, "menu"));
  menuContainer.innerHTML = "";
  specialsContainer.innerHTML = "";

  menuSnap.forEach(docSnap => {
    const item = docSnap.data();
    const id = docSnap.id;
    const isSpecial = item.isSpecial === true;

    const card = document.createElement("div");
    card.className = "col";
    card.innerHTML = `
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title d-flex justify-content-between">
            <span>${item.name}</span>
            <span class="badge bg-success">৳${item.price}</span>
          </h5>
          <p class="card-text small mb-2">${item.description || ""}</p>
          ${isSpecial ? '<span class="badge bg-warning text-dark mb-2">Today\'s Special</span>' : ""}
          <button class="btn btn-sm btn-success w-100 order-btn" data-id="${id}">
            Order
          </button>
        </div>
      </div>
    `;
    menuContainer.appendChild(card);

    if (isSpecial) {
      const sp = document.createElement("div");
      sp.className = "col-12 col-md-4";
      sp.innerHTML = `
        <div class="card border-warning h-100">
          <div class="card-body">
            <h5 class="card-title">${item.name}</h5>
            <p class="card-text small">${item.description || ""}</p>
            <span class="badge bg-success">৳${item.price}</span>
          </div>
        </div>
      `;
      specialsContainer.appendChild(sp);
    }
  });

  document.querySelectorAll(".order-btn").forEach(btn => {
    btn.addEventListener("click", () => openOrderPrompt(btn.dataset.id));
  });
}

// 1. Order button click handler for modal show
document.querySelectorAll(".order-btn").forEach(btn => {
    btn.addEventListener("click", function() {
      document.getElementById("orderMenuId").value = btn.dataset.id || btn.parentNode.querySelector(".card-title")?.innerText || "";
      document.getElementById("orderTableNumber").value = "";
      document.getElementById("orderPayment").value = "";
      let modal = new bootstrap.Modal(document.getElementById("orderModal"));
      modal.show();
    });
  });
  
  // 2. Modal submit handler
  document.getElementById("orderForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const table = document.getElementById("orderTableNumber").value;
    const payment = document.getElementById("orderPayment").value;
    alert(`Order Confirmed!\nTable: ${table}\nPayment: ${payment}`);
    bootstrap.Modal.getInstance(document.getElementById("orderModal")).hide();
  });
  
let selectedMenuId = null;

function openOrderPrompt(menuId) {
  selectedMenuId = menuId;
  document.getElementById("orderMenuId").value = menuId;
  document.getElementById("orderTableNumber").value = "";
  document.getElementById("orderPayment").value = "";
  let modal = new bootstrap.Modal(document.getElementById("orderModal"));
  modal.show();
}


const tablesContainer = document.getElementById("tablesContainer");

async function loadTables() {
  const tablesSnap = await getDocs(collection(db, "tables"));
  tablesContainer.innerHTML = "";
  tablesSnap.forEach(docSnap => {
    const table = docSnap.data();
    const status = table.isOccupied ? "Occupied" : "Available";
    const badgeClass = table.isOccupied ? "bg-danger" : "bg-success";

    const div = document.createElement("div");
    div.className = "col";
    div.innerHTML = `
      <div class="card text-center py-3 ${table.isOccupied ? "table-occupied" : "table-free"}">
        <h5>Table ${table.number}</h5>
        <span class="badge ${badgeClass}">${status}</span>
      </div>
    `;
    tablesContainer.appendChild(div);
  });
}

const ordersContainer = document.getElementById("ordersContainer");

function listenToMyOrders() {
  const qOrders = query(
    collection(db, "orders"),
    where("userId", "==", currentUserId)
  );
  onSnapshot(qOrders, snap => {
    ordersContainer.innerHTML = "";
    snap.forEach(docSnap => {
      const order = docSnap.data();
      const li = document.createElement("div");
      li.className = "list-group-item d-flex justify-content-between align-items-center";
      li.innerHTML = `
        <div>
          <div class="fw-semibold">Order: ${docSnap.id}</div>
          <div class="small text-muted">Table ${order.tableNumber}</div>
        </div>
        <span class="badge bg-primary text-capitalize">${order.status}</span>
      `;
      ordersContainer.appendChild(li);
    });
  });
}

const complaintForm = document.getElementById("complaintForm");
const complaintName = document.getElementById("complaintName");
const complaintEmail = document.getElementById("complaintEmail");
const complaintOrderId = document.getElementById("complaintOrderId");
const complaintText = document.getElementById("complaintText");
const complaintImage = document.getElementById("complaintImage");
const complaintMsg = document.getElementById("complaintMsg");

if (complaintForm) {
  complaintForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    complaintMsg.textContent = "";
    complaintMsg.className = "text-success ms-2";

    // Validate required fields
    const name = complaintName ? complaintName.value.trim() : '';
    const email = complaintEmail ? complaintEmail.value.trim() : '';
    const message = complaintText ? complaintText.value.trim() : '';

    if (!name || !email || !message) {
      complaintMsg.textContent = "Please fill in all required fields.";
      complaintMsg.className = "text-danger ms-2";
      return;
    }

    // Create FormData for file upload
    const formData = new FormData(complaintForm);

    try {
      const response = await fetch('submit_complaint.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        // Clear all form fields
        complaintForm.reset();
        if (complaintName) complaintName.value = '';
        if (complaintEmail) complaintEmail.value = '';
        if (complaintOrderId) complaintOrderId.value = '';
        if (complaintText) complaintText.value = '';
        if (complaintImage) complaintImage.value = '';

        // Show success modal
        const successModal = new bootstrap.Modal(document.getElementById('complaintSuccessModal'));
        successModal.show();

        // Clear any error messages
        complaintMsg.textContent = "";
      } else {
        // Show error message
        complaintMsg.textContent = result.message || "Error submitting complaint.";
        complaintMsg.className = "text-danger ms-2";
      }
    } catch (err) {
      console.error(err);
      complaintMsg.textContent = "Error submitting complaint. Please try again.";
      complaintMsg.className = "text-danger ms-2";
    }
  });
}
// Daily specials slider fill code
const specialsSliderInner = document.getElementById("specialsSliderInner");

async function loadSpecialsSlider() {
  // Assume menu items with isSpecial=true
  const snap = await getDocs(collection(db, "menu"));
  let activeAdded = false;
  specialsSliderInner.innerHTML = "";
  snap.forEach(docSnap => {
    const item = docSnap.data();
    if (item.isSpecial) {
      const slide = document.createElement("div");
      slide.className = "carousel-item" + (activeAdded ? "" : " active");
      activeAdded = true;
      // If item.imageUrl nai, ekta default image din
      slide.innerHTML = `
        <img src="${item.imageUrl || "https://images.unsplash.com/photo-1519864600261-12a6ce0b7bff"}" class="d-block w-100" style="height:350px; object-fit:cover;">
        <div class="carousel-caption bg-dark bg-opacity-50 rounded">
          <h5>${item.name}</h5>
          <p>${item.description || ""}</p>
          <span class="badge bg-warning text-dark fs-5">৳${item.price}</span>
        </div>
      `;
      specialsSliderInner.appendChild(slide);
    }
  });
}

document.getElementById("orderForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const tableNumber = document.getElementById("orderTableNumber").value.trim();
    const paymentMethod = document.getElementById("orderPayment").value;
    if (!tableNumber || !paymentMethod || !selectedMenuId) return;
  
    await addDoc(collection(db, "orders"), {
      userId: currentUserId,
      menuItemId: selectedMenuId,
      tableNumber,
      paymentMethod,
      status: "pending",
      createdAt: serverTimestamp()
    });
  
    // Success animation
    document.querySelector("#orderModal .modal-title").textContent = "Order Successful!";
    setTimeout(() => {
      bootstrap.Modal.getInstance(document.getElementById("orderModal")).hide();
      document.querySelector("#orderModal .modal-title").textContent = "Place Order";
    }, 1200);
  });
  
loadSpecialsSlider();

loadMenu();
loadTables();
listenToMyOrders();
