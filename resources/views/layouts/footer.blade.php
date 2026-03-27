<!-- Wrapper End-->
<footer class="iq-footer">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item"><a href="../backend/privacy-policy.html">Privacy Policy</a></li>
                            <li class="list-inline-item"><a href="../backend/terms-of-service.html">Terms of Use</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-6 text-right">
                        <span class="mr-1">
                            <script>
                                document.write(new Date().getFullYear())
                            </script>©
                        </span> <a href="#" class="">LiquorHub</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<script>
    (function() {
        if (window.__buttonTitleTooltipInitializer) {
            return;
        }

        window.__buttonTitleTooltipInitializer = true;

        function getButtonTooltipText(button) {
            const explicitLabel = button.getAttribute('aria-label') || button.getAttribute('value');
            const text = (button.textContent || '').replace(/\s+/g, ' ').trim();
            const label = explicitLabel || text;

            return label ? label.slice(0, 255) : '';
        }

        function applyButtonTitles(root) {
            const scope = root && root.querySelectorAll ? root : document;
            const buttons = scope.matches && scope.matches('button, input[type="button"], input[type="submit"], input[type="reset"]')
                ? [scope]
                : scope.querySelectorAll('button, input[type="button"], input[type="submit"], input[type="reset"]');

            buttons.forEach(function(button) {
                if (button.hasAttribute('title')) {
                    return;
                }

                const tooltipText = getButtonTooltipText(button);
                if (tooltipText) {
                    button.setAttribute('title', tooltipText);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                applyButtonTitles(document);
            });
        } else {
            applyButtonTitles(document);
        }

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        applyButtonTitles(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    })();
</script>