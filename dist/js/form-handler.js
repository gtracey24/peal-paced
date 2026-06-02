document.getElementById("contact-form").addEventListener("submit", async function (e) {
  e.preventDefault();

  const form = e.target;
  const status = document.getElementById("form-status");
  const button = document.getElementById("submit-btn");

  // Honeypot
  if (form.website.value !== "") return;

  // Timestamp check
  const ts = parseInt(form.ts.value, 10);
  if (Date.now() / 1000 - ts < 1) return;

  const data = {
    name: form.name.value.trim(),
    email: form.email.value.trim(),
    phone: form.phone.value.trim(),
    message: form.message.value.trim(),
    website: form.website.value,
    ts: form.ts.value
  };

  button.disabled = true;
  status.innerHTML = `<div class="text-muted">Sending...</div>`;

  try {
    const response = await fetch("php/form-handler.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.success) {
      status.innerHTML = `<div class="alert alert-success">Message sent successfully!</div>`;
      form.reset();
    } else {
      status.innerHTML = `<div class="alert alert-danger">${result.error || "Something went wrong."}</div>`;
    }
  } catch (error) {
    status.innerHTML = `<div class="alert alert-danger">Network error. Try again.</div>`;
  }

  button.disabled = false;
});
