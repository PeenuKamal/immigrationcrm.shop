(function () {
  var MAX = 4;
  var root = document.getElementById("picker");
  if (!root) return;
  var options = JSON.parse(document.getElementById("cmp-options").textContent);
  var picked = [];
  var init = root.getAttribute("data-initial");
  if (init && options.some(function (o) { return o.slug === init; })) picked.push(init);
  var q = document.getElementById("cmp-q");
  var results = document.getElementById("pick-results");
  var pickedEl = document.getElementById("picked");
  var label = document.getElementById("picked-label");
  var go = document.getElementById("cmp-go");
  function nameOf(s) { var o = options.find(function (x) { return x.slug === s; }); return o ? o.name : s; }
  function el(tag, attrs, text) { var n = document.createElement(tag); for (var k in attrs) n.setAttribute(k, attrs[k]); if (text != null) n.textContent = text; return n; }
  function render() {
    pickedEl.innerHTML = "";
    picked.forEach(function (s) {
      var b = el("button", { type: "button", "aria-label": "Remove " + nameOf(s) }, nameOf(s) + " ×");
      b.onclick = function () { picked = picked.filter(function (p) { return p !== s; }); render(); };
      pickedEl.appendChild(b);
    });
    label.textContent = picked.length === 0 ? "Choose 2 to 4 products." : picked.length + " of " + MAX + " chosen.";
    var n = q.value.trim().toLowerCase();
    var list = options.filter(function (o) { return !n || o.name.toLowerCase().indexOf(n) >= 0 || o.cat.toLowerCase().indexOf(n) >= 0; }).slice(0, 40);
    results.innerHTML = "";
    list.forEach(function (o) {
      var on = picked.indexOf(o.slug) >= 0;
      var li = el("li");
      var b = el("button", { type: "button" });
      b.appendChild(el("span", {}, o.name));
      b.appendChild(el("span", { class: "small muted" }, on ? "Added" : o.cat));
      if (on || picked.length >= MAX) b.disabled = true;
      b.onclick = function () { picked.push(o.slug); render(); q.focus(); };
      li.appendChild(b);
      results.appendChild(li);
    });
    if (!list.length) results.appendChild(el("li", { class: "small muted", style: "padding:12px" }, "No software matches “" + q.value + "”."));
    go.disabled = picked.length < 2;
    go.textContent = picked.length < 2 ? "Choose at least 2 to compare" : "Compare " + picked.length + " products";
  }
  q.addEventListener("input", render);
  go.addEventListener("click", function () { window.location.href = "/compare/" + picked.slice().sort().join("-vs-"); });
  render();
})();
