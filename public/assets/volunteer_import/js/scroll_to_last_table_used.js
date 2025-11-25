document.addEventListener('DOMContentLoaded', () => {

    /* ------------------------------------------------------------------
       ⭐ READ SERVER-PASSED VALUES FROM META TAGS
    ------------------------------------------------------------------ */
    const scrollToInvalid = document.getElementById('scrollToInvalid').content === '1';
    const lastUpdatedTable = document.getElementById('lastUpdatedTable').content;
    const lastUpdatedIndices = JSON.parse(document.getElementById('lastUpdatedIndices').content || '[]');

    /* ------------------------------------------------------------------
       ⭐ 1. FORCE SCROLL TO INVALID SECTION WHEN resetImports() RUNS
    ------------------------------------------------------------------ */
    if (scrollToInvalid) {
        const invalidSection = document.getElementById('import-Section-invalid');

        if (invalidSection) {
            setTimeout(() => {
                invalidSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 200);
        }

        sessionStorage.removeItem('lastUsedTable');
    }


    /* ------------------------------------------------------------------
       ⭐ 2. NORMAL LAST-USED-TABLE BEHAVIOR
    ------------------------------------------------------------------ */
    const persistKey = 'lastUsedTable';
    const persistenceCount = 2;


    /* ------------------------------------------------------------------
       🔥 CASE A: Controller explicitly sent us update data
    ------------------------------------------------------------------ */
    if (lastUpdatedTable && lastUpdatedIndices.length > 0) {

        sessionStorage.setItem(
            persistKey,
            JSON.stringify({
                type: lastUpdatedTable,
                index: lastUpdatedIndices,
                remaining: persistenceCount
            })
        );

        lastUpdatedIndices.forEach(i => {
            const tbl = document.getElementById(lastUpdatedTable + '-entries-table');
            if (tbl) {
                const row = tbl.querySelectorAll('tbody tr')[i];
                if (row) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.style.backgroundColor = '#fff3cd';
                    setTimeout(() => row.style.backgroundColor = '', 2000);
                }
            }
        });
    }


    /* ------------------------------------------------------------------
       🔥 CASE B: Use stored sessionStorage if flash is missing
    ------------------------------------------------------------------ */
    let stored = sessionStorage.getItem(persistKey);

    if (stored) {
        try {
            stored = JSON.parse(stored);

            if (stored.remaining > 0) {
                stored.remaining--;
                sessionStorage.setItem(persistKey, JSON.stringify(stored));

                const tbl = document.getElementById(stored.type + '-entries-table');
                if (tbl && stored.index) {

                    stored.index.forEach(i => {
                        const row = tbl.querySelectorAll('tbody tr')[i];
                        if (row) {
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            row.style.transition = "background-color 0.5s";
                            row.style.backgroundColor = '#fff3cd';
                            setTimeout(() => row.style.backgroundColor = '', 2000);
                        }
                    });
                }

            } else {
                sessionStorage.removeItem(persistKey);
            }
        } catch (e) {
            sessionStorage.removeItem(persistKey);
        }
    }

    // Make global helper available
    window.lastUsedTable = stored || { type: null, index: null };
});


/* ------------------------------------------------------------------
   ⭐ Helper: Update lastUsedTable explicitly (from Edit buttons etc.)
------------------------------------------------------------------ */
window.setLastUsedTable = function(type, index) {
    sessionStorage.setItem(
        'lastUsedTable',
        JSON.stringify({ type, index, remaining: 2 })
    );
};
