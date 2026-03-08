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
                credentials: 'include',
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
                credentials: 'include',
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
     * Send message to AI (optionally for a conversation; backend creates one if needed)
     */
    static async sendMessage(question, conversationId) {
        try {
            if (!question || !question.trim()) {
                throw new Error('Please enter a message');
            }

            const body = { question: question.trim() };
            if (conversationId) body.conversation_id = conversationId;

            const response = await fetch(API_BASE + '/spaceai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(body)
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

    /**
     * Check if user is logged in (uses session cookie)
     */
    static async authCheck() {
        const response = await fetch(API_BASE + '/auth-check.php', {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json().catch(() => ({}));
        if (response.status === 401 || !data.logged_in) {
            return { logged_in: false };
        }
        return { logged_in: true, username: data.username || '' };
    }

    /**
     * Logout and clear session cookie
     */
    static async logout() {
        await fetch(API_BASE + '/logout.php', { method: 'POST', credentials: 'include' });
    }

    /**
     * Get messages for a conversation (or legacy list if no id)
     */
    static async getChats(conversationId, limit) {
        let url = API_BASE + '/get-chats.php?limit=' + (limit || 500);
        if (conversationId) url += '&conversation_id=' + conversationId;
        const response = await fetch(url, { method: 'GET', credentials: 'include' });
        const data = await response.json().catch(() => ({ success: false, messages: [] }));
        return response.ok ? data : { success: false, messages: [] };
    }

    /**
     * List user's conversations
     */
    static async getConversations() {
        const response = await fetch(API_BASE + '/conversations.php', { method: 'GET', credentials: 'include' });
        const data = await response.json().catch(() => ({ success: false, conversations: [] }));
        return response.ok ? data : { success: false, conversations: [] };
    }

    /**
     * Create a new conversation
     */
    static async createConversation() {
        const response = await fetch(API_BASE + '/conversations.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Failed to create conversation');
        }
        return data.conversation || null;
    }

    /**
     * Delete a conversation and its messages
     */
    static async deleteConversation(conversationId) {
        const response = await fetch(API_BASE + '/conversations.php', {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: conversationId })
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Failed to delete chat');
        }
        return data;
    }
}
