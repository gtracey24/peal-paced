document.getElementById("contact-form").addEventListener("submit", async function (e) {
  e.preventDefault();

  const form = e.target;
  const status = document.getElementById("form-status");
  const button = document.getElementById("submit-btn");

  // Honeypot check
  if (form.website.value !== "") {
    return; // bot detected
  }

  // Timestamp check (must be > 1 second)
  const ts = parseInt(form.ts.value, 10);
  if (Date.now() / 1000 - ts < 1) {
    return; // bot detected
  }

  const data = {
    name: form.name.value.trim(),
    email: form.email.value.trim(),
    phone: form.phone.value.trim(),
    message: form.message.value.trim(),
    website: form.website.value,
    ts: form.ts.value
  };

  // Disable button + show sending state
  button.disabled = true;
  status.innerHTML = `<div class="text-muted">Sending...</div>`;

  try {
    const response = await fetch("/php/form-handler.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.status === "success") {
      status.innerHTML = `<div class="alert alert-success">Message sent successfully!</div>`;
      form.reset();
    } else {
      status.innerHTML = `<div class="alert alert-danger">${result.message || "Something went wrong."}</div>`;
    }
  } catch (error) {
    status.innerHTML = `<div class="alert alert-danger">Network error. Try again.</div>`;
  }

  // Re-enable button
  button.disabled = false;
});
