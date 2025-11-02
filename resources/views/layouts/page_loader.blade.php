<div id="page-loader">
    <div class="spinner"></div>
</div>

<style>
    #page-loader {
        position: fixed;
        top:0; left:0;
        width:100%; height:100%;
        background:#fff;
        display:flex;
        justify-content:center;
        align-items:center;
        z-index:9999;
        opacity:1;
        transition: opacity 0.5s ease;
    }
    #page-loader.hidden { opacity:0; pointer-events:none; }

    #page-loader .spinner {
        border:6px solid #f3f3f3;
        border-top:6px solid #B2000C;
        border-radius:50%;
        width:50px; height:50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin { 0%{transform:rotate(0deg);}100%{transform:rotate(360deg);} }

    section { opacity:0; transition:opacity 0.5s ease; }
    section.visible { opacity:1; }
</style>

<script>
    window.addEventListener('load', () => {
        const loader = document.getElementById('page-loader');
        if(loader) loader.classList.add('hidden');

        document.querySelectorAll('section').forEach((sec, index) => {
            setTimeout(() => sec.classList.add('visible'), index * 150);
        });
    });
</script>
