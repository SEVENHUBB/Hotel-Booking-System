// js/guest-picker.js
document.addEventListener("DOMContentLoaded", function () {
  const trigger = document.getElementById("guestsTrigger");
  const popup = document.getElementById("guestPicker");
  const display = document.getElementById("guestDisplay");
  const doneBtn = popup.querySelector(".done-btn");

  // 当前数据
  let rooms = 1, adults = 2, children = 0;

  // 更新显示文字
  function updateDisplay() {
    let text = "";
    if (adults > 0) text += `${adults} adult${adults > 1 ? "s" : ""}`;
    if (children > 0) text += ` · ${children} child${children > 1 ? "ren" : ""}`;
    if (rooms > 1 || text === "") text = `${rooms} room${rooms > 1 ? "s" : ""}` + (text ? ` · ${text}` : "");
    display.textContent = text || "Guests";
  }

  // 点击外部关闭
  document.addEventListener("click", function (e) {
    if (!trigger.contains(e.target) && !popup.contains(e.target)) {
      popup.classList.remove("show");
      trigger.classList.remove("active");
    }
  });

  // 点击触发器打开/关闭
  trigger.addEventListener("click", function (e) {
    e.stopPropagation();
    const isOpen = popup.classList.toggle("show");
    trigger.classList.toggle("active", isOpen);
  });

  // 加减按钮
  popup.addEventListener("click", function (e) {
    if (!e.target.classList.contains("counter-btn")) return;
    e.preventDefault();

    const type = e.target.dataset.type;
    const valueEl = e.target.parentNode.querySelector(".counter-value");
    let val = parseInt(valueEl.textContent);

    if (e.target.classList.contains("plus")) {
      if (type === "rooms" && rooms < 8) val++;
      if (type === "adults" && adults < 30) val++;
      if (type === "children" && children < 10) val++;
    } else if (e.target.classList.contains("minus")) {
      if (val > (type === "rooms" ? 1 : type === "adults" ? 1 : 0)) val--;
    }

    valueEl.textContent = val;

    if (type === "rooms") rooms = val;
    if (type === "adults") adults = val;
    if (type === "children") children = val;

    // 动态生成儿童年龄选择（可选功能，后面可以再加）
    updateDisplay();
  });

  // Done 按钮
  doneBtn.addEventListener("click", function () {
    popup.classList.remove("show");
    trigger.classList.remove("active");
    updateDisplay();
  });

  // 初始化
  updateDisplay();
});