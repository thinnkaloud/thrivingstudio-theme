<?php
/*
Template Name: Contact Page
*/
get_header(); ?>



<main class="flex-1 bg-gradient-to-br from-slate-50 to-white">
    <div class="site-content container mx-auto px-4 sm:px-6 lg:px-8 py-16">
        
        <!-- Hero Section -->
        <section class="text-center mb-16">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    Let's Start a 
                    <span class="bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
                        Conversation
                    </span>
                </h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Have a question, project idea, or just want to say hello? 
                    We'd love to hear from you and help bring your vision to life.
                </p>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12 border border-gray-100">
                
                <!-- Form Header -->
                <div class="text-center mb-12">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">
                        Send Us a Message
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        Fill out the form below and we'll get back to you within 24 hours. 
                        Your ideas and questions are important to us.
                    </p>
                </div>

                <!-- Contact Form -->
                <form class="space-y-6" action="#" method="POST" id="contact-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white placeholder-gray-400"
                                   placeholder="Your full name">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white placeholder-gray-400"
                                   placeholder="your.email@example.com">
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
                            Subject
                        </label>
                        <input type="text" id="subject" name="subject" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="What's this about?">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                            Message <span class="text-red-500">*</span>
                        </label>
                        <textarea id="message" name="message" rows="6" required 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white resize-none placeholder-gray-400"
                                  placeholder="Tell me about your project, question, or idea..."></textarea>
                    </div>
                    
                    <div class="text-center pt-4">
                        <button type="submit" 
                                class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-orange-500 to-yellow-500 text-black font-semibold rounded-xl hover:from-orange-600 hover:to-yellow-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            Send Message
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Contact Info Section -->
        <section class="mt-16">
            <div class="max-w-4xl mx-auto">
                
                <!-- Email Contact -->
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 text-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Us</h3>
                    <p class="text-gray-600 mb-4">Perfect for detailed discussions</p>
                    <span class="text-gray-900 font-medium">
                        hello@thrivingstudio.xyz
                    </span>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
// Simple form handling
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const name = formData.get('name');
    const email = formData.get('email');
    const subject = formData.get('subject');
    const message = formData.get('message');
    
    // Simple validation
    if (!name || !email || !message) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Show success message (you can replace this with actual form submission)
    alert('Thank you for your message! We\'ll get back to you soon.');
    this.reset();
});
</script>



<?php get_footer(); ?> 