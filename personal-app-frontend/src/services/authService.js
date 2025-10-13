// personal-app-frontend/src/services/authService.js
import api from "./api";

export const authService = {
    async login(email, password) {
        // Sanctum CSRF cookie - используем полный URL
        await api.get("/sanctum/csrf-cookie", { baseURL: "http://localhost" });
        
        const response = await api.post("/login", { 
            email: email, 
            password: password 
        });
        return response.data;
    },

    async logout() {
        await api.post("/logout");
    },

    async getUser() {
        const response = await api.get("/user");
        return response.data;
    }
};
