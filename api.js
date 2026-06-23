// api.js - Frontend API Client
const API = {
    // GET requests
    async getPendingRecords() {
        const res = await fetch('api.php?resource=pending');
        if (!res.ok) throw new Error(await res.text());
        return res.json();
    },

    async getCompletedRecords() {
        const res = await fetch('api.php?resource=completed');
        if (!res.ok) throw new Error(await res.text());
        return res.json();
    },

    async getLabs() {
        const res = await fetch('api.php?resource=labs');
        if (!res.ok) throw new Error(await res.text());
        return res.json();
    },

    async getUsers() {
        const res = await fetch('api.php?resource=users');
        if (!res.ok) throw new Error(await res.text());
        return res.json();
    },

    // POST requests
    async createLab(labName, ahfozNumber) {
        const res = await fetch('api.php?action=create_lab', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lab_name: labName, ahfoz_number: ahfozNumber })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    },

    async deleteLab(labName) {
        const res = await fetch('api.php?action=delete_lab', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lab_name: labName })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    },

    async createUser(name, role, lab, password) {
        const res = await fetch('api.php?action=create_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, role, lab, password })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    },

    async deleteUser(userId) {
        const res = await fetch('api.php?action=delete_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    },

    async verifyRecord(accessionId, scientistNotes) {
        const res = await fetch('api.php?action=verify_record', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accessionId, scientistNotes })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    },

    async rejectRecord(accessionId, scientistNotes) {
        const res = await fetch('api.php?action=reject_record', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accessionId, scientistNotes })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    },

    async recheckRecord(accessionId, scientistNotes) {
        const res = await fetch('api.php?action=recheck_record', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accessionId, scientistNotes })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data;
    }
};
