document.addEventListener("DOMContentLoaded", function () {
  var forms = document.querySelectorAll(".contact-form");

  function setError(field, message) {
    var holder = field.parentElement.querySelector(".field-error");
    field.classList.toggle("is-invalid", Boolean(message));
    if (holder) {
      holder.textContent = message || "";
    }
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
  }

  function isValidPhone(value) {
    return /^[+()\-\s0-9]{7,20}$/.test(value);
  }

  function validateField(field) {
    var value = field.value.trim();
    var label = field.closest("div").querySelector("label");
    var fieldName = label ? label.textContent.replace("*", "").trim() : "This field";

    if (field.required && !value) {
      setError(field, fieldName + " is required.");
      return false;
    }

    if (field.type === "email" && value && !isValidEmail(value)) {
      setError(field, "Enter a valid email address.");
      return false;
    }

    if (field.type === "tel" && value && !isValidPhone(value)) {
      setError(field, "Enter a valid phone number.");
      return false;
    }

    setError(field, "");
    return true;
  }

  forms.forEach(function (form) {
    var fields = form.querySelectorAll("input:not([type='hidden']), textarea, select");

    fields.forEach(function (field) {
      field.addEventListener("blur", function () {
        validateField(field);
      });

      field.addEventListener("input", function () {
        if (field.classList.contains("is-invalid")) {
          validateField(field);
        }
      });
    });

    form.addEventListener("submit", function (event) {
      var valid = true;

      fields.forEach(function (field) {
        if (!validateField(field)) {
          valid = false;
        }
      });

      if (!valid) {
        event.preventDefault();
        var firstInvalid = form.querySelector(".is-invalid");
        if (firstInvalid) {
          firstInvalid.focus();
        }
      }
    });
  });
});
