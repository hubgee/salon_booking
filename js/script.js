// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  
  // ========== Booking Form AJAX Submission ==========
  const bookingForm = document.getElementById('booking-form');
  const messageDiv = document.getElementById('booking-message');
  const submitBtn = document.getElementById('submit-btn');
  const btnText = submitBtn?.querySelector('.btn-text');
  const spinner = submitBtn?.querySelector('.spinner-border-sm');

  if (bookingForm) {
    bookingForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Hide previous messages
      messageDiv.classList.add('d-none');
      messageDiv.classList.remove('alert-success', 'alert-danger');
      
      // Validate form
      const name = document.getElementById('name').value.trim();
      const service = document.getElementById('service').value;
      const date = document.getElementById('date').value;
      const time = document.getElementById('time').value;

      // Client-side validation
      if (!name || !service || !date || !time) {
        showMessage('Please fill in all required fields.', 'error');
        return;
      }

      // Check if date is in the future
      const selectedDate = new Date(date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (selectedDate < today) {
        showMessage('Please select a future date.', 'error');
        return;
      }

      // Show loading state
      if (submitBtn) {
        submitBtn.disabled = true;
        if (btnText) btnText.textContent = 'Processing...';
        if (spinner) spinner.classList.remove('d-none');
      }

      // Prepare form data
      const formData = new FormData(bookingForm);

      try {
        const response = await fetch('process_booking.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          // Success - show success message
          const bookingInfo = data.booking;
          const successMsg = `🎉 Booking Confirmed!<br><br>
            <strong>Thank you, ${bookingInfo.name}!</strong><br>
            Your appointment for <strong>${bookingInfo.service}</strong><br>
            on <strong>${formatDate(bookingInfo.date)}</strong> at <strong>${formatTime(bookingInfo.time)}</strong><br>
            has been booked successfully.<br><br>
            <small>See you soon!</small>`;
          
          showMessage(successMsg, 'success');
          
          // Reset form
          bookingForm.reset();
          
          // Scroll to message
          messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
          // Error
          showMessage(data.message || 'An error occurred. Please try again.', 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        showMessage('Network error. Please check your connection and try again.', 'error');
      } finally {
        // Reset button state
        if (submitBtn) {
          submitBtn.disabled = false;
          if (btnText) btnText.textContent = 'Confirm Booking';
          if (spinner) spinner.classList.add('d-none');
        }
      }
    });
  }

  // ========== Show Message Function ==========
  function showMessage(message, type) {
    messageDiv.innerHTML = message;
    messageDiv.classList.remove('d-none');
    messageDiv.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    
    // Auto-hide success messages after 10 seconds
    if (type === 'success') {
      setTimeout(() => {
        messageDiv.classList.add('d-none');
      }, 10000);
    }
  }

  // ========== Helper Functions ==========
  function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('en-US', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  }

  function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
  }

  // ========== Set minimum date to today ==========
  const dateInput = document.getElementById('date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
  }

  // ========== Navbar background on scroll ==========
  const navbar = document.getElementById('mainNav');
  if (navbar) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }
});
