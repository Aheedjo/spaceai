/**
 * API Client for SpaceAI
 * Handles all backend API calls
 */

const API_BASE = ''; // Relative path to PHP files

class SpaceAIAPI {
    /**
     * Register a new user
     */
    static async register(username, password) {
        try {
            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Registration failed');
            }
            return data;
        } catch (error) {
            console.error('Registration error:', error);
            throw error;
        }
    }

    /**
     * Login user
     */
    static async login(username, password) {
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }
            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    /**
     * Send message to AI
     */
    static async sendMessage(question) {
        try {
            // Validate question
            if (!question || !question.trim()) {
                throw new Error('Please enter a message');
            }

            const response = await fetch('spaceai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include', // Include cookies for session
                body: JSON.stringify({ question: question.trim() })
            });

            // Get response as text first to check if it's JSON
            const responseText = await response.text();
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                // Response is not JSON - likely a PHP error
                console.error('Non-JSON response:', responseText);
                throw new Error('Server error: ' + (responseText.substring(0, 100) || 'Invalid response format'));
            }

            if (!response.ok) {
                throw new Error(data.answer || data.message || 'Failed to get AI response');
            }
            return data;
        } catch (error) {
            console.error('AI API error:', error);
            throw error;
        }
    }

    /**
     * Check if user is logged in
     */
    static async checkAuth() {
        try {
            // This would require a separate endpoint, for now we'll rely on session
            return true;
        } catch (error) {
            return false;
        }
    }
}

