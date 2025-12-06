// API Helper for HRMS Frontend

const API = {
    baseUrl: '../backend/index.php',
    
    async request(action, method = 'GET', data = null, resource = '', id = 0) {
        let url = `${this.baseUrl}?action=${action}`;
        if (resource) url += `&resource=${resource}`;
        if (id) url += `&id=${id}`;
        
        const options = {
            method: method,
            headers: {}
        };
        
        if (data instanceof FormData) {
            // For file uploads, don't set Content-Type
            options.body = data;
        } else if (data) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    // Auth
    async login(role, email, password) {
        return this.request('login', 'POST', { role, email, password });
    },
    
    async logout() {
        return this.request('logout', 'POST');
    },
    
    async checkSession() {
        return this.request('check-session', 'GET');
    },
    
    // Dashboard
    async getDashboardStats() {
        return this.request('dashboard', 'GET');
    },
    
    // Employees
    async getEmployees() {
        return this.request('employees', 'GET');
    },
    
    async getEmployee(id) {
        return this.request('employees', 'GET', null, '', id);
    },
    
    async createEmployee(data) {
        return this.request('employees', 'POST', data);
    },
    
    async updateEmployee(id, data) {
        return this.request('employees', 'PUT', data, '', id);
    },
    
    async deleteEmployee(id) {
        return this.request('employees', 'DELETE', null, '', id);
    },
    
    // Attendance
    async getAttendance() {
        return this.request('attendance', 'GET');
    },
    
    async getMyAttendance() {
        return this.request('attendance', 'GET', null, 'my');
    },
    
    async markAttendance(data) {
        return this.request('attendance', 'POST', data);
    },
    
    // Leaves
    async getLeaves() {
        return this.request('leaves', 'GET');
    },
    
    async getMyLeaves() {
        return this.request('leaves', 'GET', null, 'my');
    },
    
    async requestLeave(data) {
        return this.request('leaves', 'POST', data);
    },
    
    async approveLeave(id, comment) {
        return this.request('leaves', 'PUT', { comment }, 'approve', id);
    },
    
    async rejectLeave(id, comment) {
        return this.request('leaves', 'PUT', { comment }, 'reject', id);
    },
    
    // Project Categories
    async getProjectCategories() {
        return this.request('project-categories', 'GET');
    },
    
    async createProjectCategory(data) {
        return this.request('project-categories', 'POST', data);
    },
    
    async updateProjectCategory(id, data) {
        return this.request('project-categories', 'PUT', data, '', id);
    },
    
    async deleteProjectCategory(id) {
        return this.request('project-categories', 'DELETE', null, '', id);
    },
    
    // Projects
    async getProjects() {
        return this.request('projects', 'GET');
    },
    
    async createProject(data) {
        return this.request('projects', 'POST', data);
    },
    
    async updateProject(id, data) {
        return this.request('projects', 'PUT', data, '', id);
    },
    
    // Tasks
    async getTasks() {
        return this.request('tasks', 'GET');
    },
    
    async getMyTasks() {
        return this.request('tasks', 'GET', null, 'my');
    },
    
    async createTask(data) {
        return this.request('tasks', 'POST', data);
    },
    
    async updateTask(id, data) {
        return this.request('tasks', 'PUT', data, '', id);
    },
    
    // Documents
    async getDocuments() {
        return this.request('documents', 'GET');
    },
    
    async uploadDocument(file, empId = null) {
        const formData = new FormData();
        formData.append('file', file);
        if (empId) formData.append('empId', empId);
        return this.request('documents', 'POST', formData);
    },
    
    async deleteDocument(id) {
        return this.request('documents', 'DELETE', null, '', id);
    },
    
    // Notifications
    async getNotifications() {
        return this.request('notifications', 'GET');
    },
    
    async getUnreadCount() {
        return this.request('notifications', 'GET', null, 'count');
    },
    
    async markNotificationAsRead(id) {
        return this.request('notifications', 'PUT', null, '', id);
    },
    
    async markAllNotificationsAsRead() {
        return this.request('notifications', 'PUT', null, 'read-all');
    }
};

