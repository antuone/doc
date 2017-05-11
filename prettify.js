
window.addEventListener("load", function () {
    var code = document.getElementsByTagName('pre');

    for (var i = 0; i < code.length; i++) {
        code[i].innerHTML = prettify(code[i].innerText);
    }
});

function prettify(code) {
    var comments = [];	// Тут собираем все каменты
    var strings = [];	// Тут собираем все строки
    var res = [];	// Тут собираем все RegExp
    var all = { 'C': comments, 'S': strings, 'R': res };
    var safe = { '<': '<', '>': '>', '&': '&' };

    return code
        // Маскируем HTML
        .replace(/[<>&]/g, function (m)
        { return safe[m]; })
        // Убираем каменты
        .replace(/\/\*[\s\S]*\*\//g, function (m)
        { var l = comments.length; comments.push(m); return '~~~C' + l + '~~~'; })
        .replace(/([^\\])\/\/[^\n]*\n/g, function (m, f)
        { var l = comments.length; comments.push(m); return f + '~~~C' + l + '~~~'; })
        // Убираем строки
        .replace(/([^\\])((?:'(?:\\'|[^'])*')|(?:"(?:\\"|[^"])*"))/g, function (m, f, s)
        { var l = strings.length; strings.push(s); return f + '~~~S' + l + '~~~'; })
        // Выделяем ключевые слова
        .replace(/(var|function|typeof|new|return|if|for|in|while|break|do|continue|switch|case)([^a-z0-9\$_])/gi,
        '<span class="kwrd">$1</span>$2')
        // Выделяем скобки
        .replace(/(\{|\}|\]|\[|\|)/gi,
        '<span class="gly">$1</span>')
        // Выделяем имена функций
        .replace(/([a-z\_\$][a-z0-9_]*)[\s]*\(/gi,
        '<span class="func">$1</span>(')
        // Возвращаем на место каменты, строки, RegExp
        .replace(/~~~([CSR])(\d+)~~~/g, function (m, t, i)
        { return '<span class="' + t + '">' + all[t][i] + '</span>'; })
        // Выставляем переводы строк
        .replace(/\n/g,
        '<br/>')
        // Табуляцию заменяем неразрывными пробелами
        .replace(/\t/g,
        '&nbsp;&nbsp;&nbsp;&nbsp;');
}