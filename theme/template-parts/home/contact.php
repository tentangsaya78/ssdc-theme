<div x-data="{ 
    loading: false, 
    message: '', 
    status: '', 
    submitForm(e) {
        this.loading = true;
        this.message = '';
        let formData = new FormData(e.target);
        formData.append('action', 'handle_contact_form');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            this.loading = false;
            this.status = data.success ? 'success' : 'error';
            this.message = data.data;
        });
    }
}" class="bg-white rounded-2xl shadow-sm p-6 border border-secondary/10">

    <form @submit.prevent="submitForm($event)">
        <?php wp_nonce_field('contact_form_nonce', 'nonce'); ?>

        <h4>Please fill in the contact form below !</h4>
        
        <div class="mb-4">
            <label class="ssdc-label">Name <span class="text-red-400">*</span></label>
            <input type="text" name="name" required class="ssdc-input w-full" placeholder="Your Name">
        </div>

        <div class="mb-4">
            <label class="ssdc-label">Email <span class="text-red-400">*</span></label>
            <input type="email" name="email" required class="ssdc-input w-full" placeholder="you@example.com">
        </div>

        <div class="mb-4">
            <label class="ssdc-label">Message <span class="text-red-400">*</span></label>
            <textarea name="message" required class="ssdc-input w-full" rows="4" placeholder="How can we help?"></textarea>
        </div>

        <button type="submit" 
                :disabled="loading"
                class="w-full bg-primary text-white py-3 rounded-full font-medium hover:bg-primary/90 transition flex items-center justify-center gap-2">
            <span x-show="!loading">Send Message</span>
            <span x-show="loading" class="animate-pulse">Sending...</span>
        </button>
    </form>

    <div x-show="message" 
         x-transition 
         :class="status === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
         class="mt-4 p-4 rounded-xl text-sm text-center">
        <span x-text="message"></span>
    </div>
</div>