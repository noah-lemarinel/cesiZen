/**
 * Emotion Tracker Local Storage Manager
 * Manages local storage for emotion tracker entries with sync capability
 */

class EmotionTrackerStorage {
    constructor() {
        this.storageKey = 'emotionTrackerEntries';
        this.synced = false;
    }

    /**
     * Get all emotion entries from local storage
     */
    getAllEntries() {
        const data = localStorage.getItem(this.storageKey);
        return data ? JSON.parse(data) : [];
    }

    /**
     * Add a new emotion entry to local storage
     */
    addEntry(emotionId, emotionName, notes = '') {
        const entries = this.getAllEntries();
        const entry = {
            id: 'local_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
            emotionId: emotionId,
            emotionName: emotionName,
            notes: notes,
            createdAt: new Date().toISOString(),
            synced: false
        };
        entries.push(entry);
        localStorage.setItem(this.storageKey, JSON.stringify(entries));
        return entry;
    }

    /**
     * Remove an emotion entry from local storage
     */
    removeEntry(entryId) {
        const entries = this.getAllEntries();
        const filtered = entries.filter(e => e.id !== entryId);
        localStorage.setItem(this.storageKey, JSON.stringify(filtered));
    }

    /**
     * Get unsynced entries
     */
    getUnsyncedEntries() {
        return this.getAllEntries().filter(e => !e.synced);
    }

    /**
     * Mark entry as synced
     */
    markAsSynced(entryId) {
        const entries = this.getAllEntries();
        const entry = entries.find(e => e.id === entryId);
        if (entry) {
            entry.synced = true;
            localStorage.setItem(this.storageKey, JSON.stringify(entries));
        }
    }

    /**
     * Clear all entries from local storage
     */
    clearAll() {
        localStorage.removeItem(this.storageKey);
    }

    /**
     * Sync unsynced entries with the server
     */
    async syncWithServer() {
        const unsyncedEntries = this.getUnsyncedEntries();
        if (unsyncedEntries.length === 0) {
            return {success: true, synced: 0};
        }

        try {
            const response = await fetch('/emotion/tracker/api/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    entries: unsyncedEntries
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Mark entries as synced
            unsyncedEntries.forEach(entry => {
                this.markAsSynced(entry.id);
            });

            return {success: true, synced: unsyncedEntries.length};
        } catch (error) {
            console.error('Error syncing emotion tracker entries:', error);
            return {success: false, error: error.message};
        }
    }

    /**
     * Get saved entries count
     */
    getCount() {
        return this.getAllEntries().length;
    }

    /**
     * Format an entry for display
     */
    formatEntryForDisplay(entry) {
        const date = new Date(entry.createdAt);
        return {
            id: entry.id,
            emotionName: entry.emotionName,
            notes: entry.notes,
            createdAt: date,
            formattedDate: date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}),
            isLocal: true,
            isSynced: entry.synced
        };
    }
}

// Initialize global instance
window.emotionTrackerStorage = new EmotionTrackerStorage();

// Auto-sync when online
window.addEventListener('online', async () => {
    console.log('Connection restored - syncing emotion tracker...');
    const result = await window.emotionTrackerStorage.syncWithServer();
    if (result.success) {
        console.log(`Synced ${result.synced} emotion entries`);
        // Optionally reload or update UI
    }
});

