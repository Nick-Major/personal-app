import api from "./api";

export const workRequestService = {
    async getMyRequests() {
        try {
            const response = await api.get("/my/work-requests");
            console.log("getMyRequests response:", response);
            return response.data;
        } catch (error) {
            console.error("Error in getMyRequests:", error);
            throw error;
        }
    },

    async createRequest(requestData) {
        const response = await api.post("/work-requests", requestData);
        return response.data;
    },

    async updateRequest(id, requestData) {
        const response = await api.put(`/work-requests/${id}`, requestData);
        return response.data;
    },

    async publishRequest(id) {
        const response = await api.post(`/work-requests/${id}/publish`);
        return response.data;
    },

    async getRequestsByStatus(status) {
        const response = await api.get(`/work-requests/status/${status}`);
        return response.data;
    },

    async getRequest(id) {
        const response = await api.get(`/work-requests/${id}`);
        return response.data;
    },

    async deleteRequest(id) {
        const response = await api.delete(`/work-requests/${id}`);
        return response.data;
    }
};
