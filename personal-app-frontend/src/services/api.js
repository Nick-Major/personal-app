import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost/api", // Прямой доступ к API
    withCredentials: true,
    headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
});

// Добавляем токен к каждому запросу
api.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Обрабатываем ошибки
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem("token");
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);

export default api;
