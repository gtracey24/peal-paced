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
    const response = await fetch("https://script.google.com/macros/s/AKfycbw_6cfV7gCBKj3LmltodYt-T71xaBR9871dioP4Kl9jn1XNlyHaxmETQjV56cItb18oJQ/exec", {
      method: "POST",
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (result.status === "success") {
      status.innerHTML = `<div class="alert alert-success">Message sent successfully!</div>`;
      form.reset();
    } else {
      status.innerHTML = `<div class="alert alert-danger">Something went wrong. Try again.</div>`;
    }
  } catch (error) {
    status.innerHTML = `<div class="alert alert-danger">Network error. Try again.</div>`;
  }
});
