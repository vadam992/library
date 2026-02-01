const API_BASE = "";

const els = {
  form: document.getElementById("bookForm"),
  formTitle: document.getElementById("formTitle"),
  formHint: document.getElementById("formHint"),
  submitBtn: document.getElementById("submitBtn"),
  cancelEditBtn: document.getElementById("cancelEditBtn"),

  bookId: document.getElementById("bookId"),
  title: document.getElementById("title"),
  author: document.getElementById("author"),
  publishYear: document.getElementById("publishYear"),
  isAvailable: document.getElementById("isAvailable"),

  tbody: document.getElementById("booksTbody"),
  empty: document.getElementById("emptyState"),
  search: document.getElementById("search"),
  refreshBtn: document.getElementById("refreshBtn"),
  countLabel: document.getElementById("countLabel"),

  toast: document.getElementById("toast"),
};

function showToast(msg, type = "info") {
  els.toast.hidden = false;
  els.toast.textContent = msg;
  els.toast.style.borderColor =
    type === "error"
      ? "rgba(255,110,110,.45)"
      : type === "ok"
        ? "rgba(125,255,176,.45)"
        : "rgba(255,255,255,.18)";

  window.clearTimeout(showToast._t);
  showToast._t = window.setTimeout(() => {
    els.toast.hidden = true;
  }, 2200);
}

async function api(path, { method = "GET", body } = {}) {
  const res = await fetch(API_BASE + path, {
    method,
    headers: body ? { "Content-Type": "application/json" } : undefined,
    body: body ? JSON.stringify(body) : undefined,
  });

  const text = await res.text();
  let data;
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    data = { raw: text };
  }

  if (!res.ok) {
    const message = data?.error || `HTTP ${res.status}`;
    throw new Error(message);
  }
  return data;
}

function resetForm() {
  els.bookId.value = "";
  els.title.value = "";
  els.author.value = "";
  els.publishYear.value = "";
  els.isAvailable.checked = true;

  els.formTitle.textContent = "Új könyv hozzáadása";
  els.formHint.textContent = "Töltsd ki a mezőket, majd mentsd el.";
  els.submitBtn.textContent = "Hozzáadás";
  els.cancelEditBtn.hidden = true;
}

function fillFormForEdit(book) {
  els.bookId.value = book.ID;
  els.title.value = book.Title ?? "";
  els.author.value = book.Author ?? "";
  els.publishYear.value =
    (book.PublishYear ?? "") === null ? "" : (book.PublishYear ?? "");
  els.isAvailable.checked =
    String(book.IsAvailable) === "1" || book.IsAvailable === true;

  els.formTitle.textContent = `Szerkesztés (ID: ${book.ID})`;
  els.formHint.textContent = "Módosítsd a mezőket, majd mentsd el.";
  els.submitBtn.textContent = "Mentés";
  els.cancelEditBtn.hidden = false;

  els.title.focus();
}

function renderRows(books) {
  els.tbody.innerHTML = "";

  if (!books || books.length === 0) {
    els.empty.hidden = false;
    els.countLabel.textContent = "0 könyv";
    return;
  }

  els.empty.hidden = true;
  els.countLabel.textContent = `${books.length} könyv`;

  for (const b of books) {
    const tr = document.createElement("tr");

    const isAvail = String(b.IsAvailable) === "1" || b.IsAvailable === true;
    const badge = isAvail
      ? `<span class="badge ok">● Elérhető</span>`
      : `<span class="badge no">● Kölcsönzött</span>`;

    tr.innerHTML = `
      <td>${b.ID}</td>
      <td>${escapeHtml(b.Title ?? "")}</td>
      <td>${escapeHtml(b.Author ?? "")}</td>
      <td>${b.PublishYear ?? ""}</td>
      <td>${badge}</td>
      <td class="right">
        <div class="actionsCell">
          <button class="btn small" data-action="edit" data-id="${b.ID}">Szerkesztés</button>
          <button class="btn small danger" data-action="delete" data-id="${b.ID}">Törlés</button>
        </div>
      </td>
    `;

    els.tbody.appendChild(tr);
  }
}

function escapeHtml(str) {
  return String(str)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

async function loadBooks() {
  const q = els.search.value.trim();
  const qs = q ? `?search=${encodeURIComponent(q)}` : "";
  const res = await api(`/api/books${qs}`);
  renderRows(res.data || []);
}

async function onSubmit(e) {
  e.preventDefault();

  const id = els.bookId.value.trim();
  const title = els.title.value.trim();
  const author = els.author.value.trim();
  const publishYearRaw = els.publishYear.value.trim();

  if (!title || !author) {
    showToast("A cím és a szerző kötelező.", "error");
    return;
  }

  const payload = {
    title,
    author,
    publishYear: publishYearRaw === "" ? null : Number(publishYearRaw),
    isAvailable: els.isAvailable.checked,
  };

  try {
    if (!id) {
      await api("/api/books", { method: "POST", body: payload });
      showToast("Könyv hozzáadva.", "ok");
    } else {
      await api(`/api/books/${id}`, { method: "PUT", body: payload });
      showToast("Könyv frissítve.", "ok");
    }

    resetForm();
    await loadBooks();
  } catch (err) {
    showToast(err.message || "Hiba történt.", "error");
  }
}

async function onTableClick(e) {
  const btn = e.target.closest("button[data-action]");
  if (!btn) return;

  const id = btn.getAttribute("data-id");
  const action = btn.getAttribute("data-action");

  try {
    if (action === "edit") {
      const res = await api(`/api/books/${id}`);
      fillFormForEdit(res.data);
    }

    if (action === "delete") {
      const ok = confirm(`Biztosan törlöd a(z) ${id} azonosítójú könyvet?`);
      if (!ok) return;

      await api(`/api/books/${id}`, { method: "DELETE" });
      showToast("Könyv törölve.", "ok");
      await loadBooks();
    }
  } catch (err) {
    showToast(err.message || "Hiba történt.", "error");
  }
}

// Keresés “debounce”
let searchTimer = null;
function onSearchInput() {
  window.clearTimeout(searchTimer);
  searchTimer = window.setTimeout(() => loadBooks(), 250);
}

// init
els.form.addEventListener("submit", onSubmit);
els.cancelEditBtn.addEventListener("click", resetForm);
els.refreshBtn.addEventListener("click", loadBooks);
els.search.addEventListener("input", onSearchInput);
els.tbody.addEventListener("click", onTableClick);

resetForm();
loadBooks().catch(() => showToast("Nem sikerült betölteni a listát.", "error"));
