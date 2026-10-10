<?php
// =========================================================
// includes/layout_footer.php
// Penutup shell layout: tutup .content & .right-panel,
// plus JS bersama (toggle sidebar mobile + jam live topbar).
// Di-include tepat sebelum </body> di tiap halaman public/.
// =========================================================
?>
    </div><!-- /.content -->
</div><!-- /.right-panel -->
<script>
(function () {
    var toggle = document.getElementById("sidebarToggle");
    if (toggle) {
        toggle.addEventListener("click", function () {
            document.body.classList.toggle("sidebar-open");
        });
    }
    var overlay = document.getElementById("sidebarOverlay");
    if (overlay) {
        overlay.addEventListener("click", function () {
            document.body.classList.remove("sidebar-open");
        });
    }
    function tick() {
        var el = document.getElementById("liveClock");
        if (!el) return;
        var d = new Date();
        try {
            el.textContent = d.toLocaleDateString("id-ID", {
                weekday: "long", day: "numeric", month: "long", year: "numeric"
            }) + " • " + d.toLocaleTimeString("id-ID");
        } catch (e) {
            el.textContent = d.toLocaleString();
        }
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
