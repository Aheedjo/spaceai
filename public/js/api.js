/**
 * API Client for SpaceAI
 * Handles all backend API calls
 */

const API_BASE = 'api'; // API folder under document root

class SpaceAIAPI {
    /**
     * Register a new user
     */
    static async register(username, password) {
        try {
            const response = await fetch(API_BASE + '/register.php', {
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
            const response = await fetch(API_BASE + '/login.php', {
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
            if (!question || !question.trim()) {
                throw new Error('Please enter a message');
            }

            const response = await fetch(API_BASE + '/spaceai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ question: question.trim() })
            });

            const responseText = await response.text();
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
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

    static async checkAuth() {
        try {
            return true;
        } catch (error) {
            return false;
        }
    }
}
