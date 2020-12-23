<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <link rel="icon" href="{{ asset('/favicon.ico')}}"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="theme-color" content="#000000"/>
    <meta name="description" content="Web site created using create-react-app"/>
    <link rel="apple-touch-icon" href="{{asset('/logo192.png')}}"/>
    <link rel="manifest" href="{{asset('/manifest.json')}}"/>
    <link href="https://fonts.googleapis.com/css?family=Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <title>Six Menu - Restaurant in Israel</title>
    <link href="{{ asset('/static/css/2.bc581860.chunk.css') }}" rel="stylesheet">
    <link href="{{ asset('/static/css/main.3d9c6ded.chunk.css') }}" rel="stylesheet">
</head>
<body>
<noscript>You need to enable JavaScript to run this app.</noscript>
<div id="root"></div>
<script>!function (f) {
        function e(e) {
            for (var r, t, n = e[0], o = e[1], u = e[2], i = 0, l = []; i < n.length; i++) t = n[i], Object.prototype.hasOwnProperty.call(p, t) && p[t] && l.push(p[t][0]), p[t] = 0;
            for (r in o) Object.prototype.hasOwnProperty.call(o, r) && (f[r] = o[r]);
            for (s && s(e); l.length;) l.shift()();
            return c.push.apply(c, u || []), a()
        }

        function a() {
            for (var e, r = 0; r < c.length; r++) {
                for (var t = c[r], n = !0, o = 1; o < t.length; o++) {
                    var u = t[o];
                    0 !== p[u] && (n = !1)
                }
                n && (c.splice(r--, 1), e = i(i.s = t[0]))
            }
            return e
        }

        var t = {}, p = {1: 0}, c = [];

        function i(e) {
            if (t[e]) return t[e].exports;
            var r = t[e] = {i: e, l: !1, exports: {}};
            return f[e].call(r.exports, r, r.exports, i), r.l = !0, r.exports
        }

        i.m = f, i.c = t, i.d = function (e, r, t) {
            i.o(e, r) || Object.defineProperty(e, r, {enumerable: !0, get: t})
        }, i.r = function (e) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {value: "Module"}), Object.defineProperty(e, "__esModule", {value: !0})
        }, i.t = function (r, e) {
            if (1 & e && (r = i(r)), 8 & e) return r;
            if (4 & e && "object" == typeof r && r && r.__esModule) return r;
            var t = Object.create(null);
            if (i.r(t), Object.defineProperty(t, "default", {
                enumerable: !0,
                value: r
            }), 2 & e && "string" != typeof r) for (var n in r) i.d(t, n, function (e) {
                return r[e]
            }.bind(null, n));
            return t
        }, i.n = function (e) {
            var r = e && e.__esModule ? function () {
                return e.default
            } : function () {
                return e
            };
            return i.d(r, "a", r), r
        }, i.o = function (e, r) {
            return Object.prototype.hasOwnProperty.call(e, r)
        }, i.p = "/";
        var r = this.webpackJsonpsixmenu = this.webpackJsonpsixmenu || [], n = r.push.bind(r);
        r.push = e, r = r.slice();
        for (var o = 0; o < r.length; o++) e(r[o]);
        var s = n;
        a()
    }([])</script>
<script src="{{ asset('/static/js/2.ea6ffb91.chunk.js') }}"></script>
<script src="{{ asset('/static/js/main.1c7d3eb9.chunk.js') }}"></script>
</body>
</html>
