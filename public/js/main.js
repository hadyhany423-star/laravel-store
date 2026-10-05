document.addEventListener("DOMContentLoaded", function () {
    // التمرير الناعم للروابط داخل الصفحة
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: "smooth" });
            }
        });
    });

    // تأكيد العمليات التي تحذف بيانات ومنع الإرسال المتكرر
    document.addEventListener("submit", function (e) {
        const form = e.target;
        const action = (form.getAttribute("action") || "").toLowerCase();
        const submitButton = form.querySelector('button[type="submit"]');
        const buttonText = submitButton ? submitButton.innerText.trim() : "";
        const isDestructive =
            action.includes("remove") ||
            action.includes("delete") ||
            action.includes("clear") ||
            buttonText.includes("حذف") ||
            buttonText.includes("إفراغ");

        if (isDestructive) {
            const isClearing =
                action.includes("clear") || buttonText.includes("إفراغ");
            const message = isClearing
                ? "هل أنت متأكد من إفراغ السلة بالكامل؟"
                : "هل أنت متأكد من حذف هذا العنصر؟";

            if (!window.confirm(message)) {
                e.preventDefault();
            }
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerText = "جاري التنفيذ...";
        }
    });
});
