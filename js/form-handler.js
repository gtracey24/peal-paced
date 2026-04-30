document.getElementById("contact-form").addEventListener("submit", async function (e) {
  e.preventDefault();

  const form = e.target;
  const status = document.getElementById("form-status");

  const data = {
    name: form.name.value,
    email: form.email.value,
    message: form.message.value
  };

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
});
