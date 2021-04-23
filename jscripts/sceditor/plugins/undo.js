/* SCEditor v2.1.3 | (C) 2017, Sam Clarke | sceditor.com/license */

!(function (e) {
	"use strict";
	sceditor.plugins.undo = function () {
		var r,
			o,
			e = this,
			u = 0,
			a = 50,
			n = [],
			c = [],
			s = !1,
			l = function (e) {
				(s = !0),
					(o = e.value),
					r.sourceMode(e.sourceMode),
					r.val(e.value, !1),
					r.focus(),
					e.sourceMode ? r.sourceEditorCaret(e.caret) : r.getRangeHelper().restoreRange(),
					(s = !1);
			};
		(e.init = function () {
			(a = (r = this).undoLimit || a),
				r.addShortcut("ctrl+z", e.undo),
				r.addShortcut("ctrl+shift+z", e.redo),
				r.addShortcut("ctrl+y", e.redo);
		}),
			(e.undo = function () {
				var e = c.pop(),
					t = r.val(null, !1);
				return (
					e && !n.length && t === e.value && (e = c.pop()),
					e &&
						(n.length ||
							n.push({
								caret: r.sourceEditorCaret(),
								sourceMode: r.sourceMode(),
								value: t,
							}),
						n.push(e),
						l(e)),
					!1
				);
			}),
			(e.redo = function () {
				var e = n.pop();
				return c.length || (c.push(e), (e = n.pop())), e && (c.push(e), l(e)), !1;
			}),
			(e.signalReady = function () {
				var e = r.val(null, !1);
				(o = e),
					c.push({
						caret: this.sourceEditorCaret(),
						sourceMode: this.sourceMode(),
						value: e,
					});
			}),
			(e.signalValuechangedEvent = function (e) {
				var t = e.detail.rawValue;
				0 < a && c.length > a && c.shift(),
					!s &&
						o &&
						o !== t &&
						((n.length = 0),
						(u += (function (e, t) {
							var r,
								o,
								u,
								a,
								n = e.length,
								c = t.length,
								s = Math.max(n, c);
							for (r = 0; r < s && e.charAt(r) === t.charAt(r); r++);
							for (
								u = n < c ? c - n : 0, a = c < n ? n - c : 0, o = s - 1;
								0 <= o && e.charAt(o - u) === t.charAt(o - a);
								o--
							);
							return o - r + 1;
						})(o, t)) < 20 ||
							(u < 50 && !/\s$/g.test(e.rawValue)) ||
							(c.push({
								caret: r.sourceEditorCaret(),
								sourceMode: r.sourceMode(),
								value: t,
							}),
							(u = 0),
							(o = t)));
			});
	};
})();
