<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Document Library – Public Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    {{-- Tailwind CDN (for quick testing) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        accent: '#22c55e',
                    },
                    boxShadow: {
                        'soft-glow': '0 22px 50px rgba(15, 23, 42, 0.8)',
                    },
                    borderRadius: {
                        'xl-soft': '18px',
                    },
                },
            },
        };
    </script>

    {{-- Extra styles only for blobs + keyframes (everything else uses Tailwind) --}}
    <style>
        body {
            background: radial-gradient(circle at top, #1e293b 0, #020617 55%, #000 100%);
        }

        .blob {
            position: fixed;
            border-radius: 999px;
            opacity: 0.4;
            filter: blur(50px);
            pointer-events: none;
            z-index: -1;
        }

        .blob-1 {
            width: 320px;
            height: 320px;
            background: #6366f1;
            top: -80px;
            left: -40px;
            animation: float 16s infinite alternate ease-in-out;
        }

        .blob-2 {
            width: 260px;
            height: 260px;
            background: #22c55e;
            bottom: -80px;
            right: -40px;
            animation: float 18s infinite alternate-reverse ease-in-out;
        }

        .blob-3 {
            width: 220px;
            height: 220px;
            background: #f97316;
            top: 35%;
            right: 10%;
            animation: float 24s infinite alternate ease-in-out;
        }

        @keyframes float {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to   { transform: translate3d(30px, -20px, 0) scale(1.1); }
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased relative overflow-x-hidden">

{{-- gradient blobs --}}
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<div class="min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-20 border-b border-slate-700/40 bg-slate-950/80 backdrop-blur-xl bg-gradient-to-b from-slate-950/90 to-transparent">
        <div class="max-w-5xl lg:max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-full bg-[conic-gradient(from_130deg,#6366f1,#22c55e,#f97316,#ec4899,#6366f1)] flex items-center justify-center text-xs font-extrabold text-white shadow-lg shadow-indigo-500/70">
                    DL
                </div>
                <div class="leading-tight">
                    <div class="font-semibold text-sm sm:text-base">Document Library</div>
                    <div class="text-[11px] text-slate-400">Public Portal · Demo UI</div>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-full border border-transparent bg-gradient-to-r from-indigo-500 to-emerald-500 px-3 py-1.5 font-medium text-white shadow-lg shadow-cyan-400/50 text-[11px] sm:text-xs hover:shadow-cyan-400/70 transition">
                    Login
                </button>
            </div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="flex-1">
        <div class="max-w-5xl lg:max-w-6xl mx-auto px-4 sm:px-6 py-4 sm:py-6">

            {{-- HERO --}}
            <section class="grid grid-cols-1 gap-6 pt-2 pb-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                        Your
                        <span class="bg-gradient-to-r from-purple-500 via-indigo-500 to-emerald-400 bg-clip-text text-transparent">
                            campus library
                        </span>,
                        but actually searchable.
                    </h1>

                    <p class="mt-3 text-sm sm:text-base text-slate-400 max-w-xl">
                        Explore books, newspapers, and research projects in a single place.
                        Guests can browse published documents without any login.
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2 text-[11px] sm:text-xs">
                        <div
                            class="inline-flex items-center gap-1 rounded-full border border-slate-500/60 bg-slate-950/80 px-2.5 py-1">
                            <span>⚡</span>
                            <span>Instant search by title, author, field</span>
                        </div>
                        <div
                            class="inline-flex items-center gap-1 rounded-full border border-slate-500/60 bg-slate-950/80 px-2.5 py-1">
                            <span>📚</span>
                            <span>Books · Newspapers · Projects</span>
                        </div>
                        <div
                            class="inline-flex items-center gap-1 rounded-full border border-slate-500/60 bg-slate-950/80 px-2.5 py-1">
                            <span>🌐</span>
                            <span>Public read-only access</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-full border border-transparent bg-gradient-to-r from-indigo-500 to-emerald-500 px-4 py-1.5 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-cyan-400/50 hover:shadow-cyan-400/70 transition">
                            Start browsing
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-500/80 bg-slate-950/90 px-4 py-1.5 text-xs sm:text-sm text-slate-100 hover:bg-slate-900 hover:shadow-lg hover:shadow-slate-900/70 transition">
                            Learn more
                        </button>
                        <p class="text-[11px] sm:text-xs text-slate-400">
                            Public page shows only published documents.
                        </p>
                    </div>
                </div>
            </section>

            {{-- SEARCH / FILTERS --}}
            <section class="mt-2 mb-4 flex flex-wrap items-center gap-3">
                {{-- Search --}}
                <div
                    class="flex items-center gap-2 rounded-full bg-slate-950/90 border border-slate-500/60 px-3 py-1.5 flex-1 min-w-[230px]">
                    <span class="text-xs">🔍</span>
                    <input
                        id="search-input"
                        type="text"
                        class="w-full bg-transparent border-none outline-none text-xs sm:text-sm text-slate-100 placeholder:text-slate-500"
                        placeholder="Search documents by title, author, field..."
                    />
                </div>

                {{-- Filters --}}
                <div class="flex flex-wrap gap-2 text-[11px] sm:text-xs">
                    <div class="relative">
                        <select
                            id="filter-field"
                            class="appearance-none rounded-full border border-slate-500/70 bg-slate-950/95 px-3 pr-7 py-1.5 text-[11px] sm:text-xs text-slate-100">
                            <option value="">All fields</option>
                            <option>Computer Science</option>
                            <option>Business</option>
                            <option>Engineering</option>
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">
                            ▾
                        </span>
                    </div>

                    <div class="relative">
                        <select
                            id="filter-genre"
                            class="appearance-none rounded-full border border-slate-500/70 bg-slate-950/95 px-3 pr-7 py-1.5 text-[11px] sm:text-xs text-slate-100">
                            <option value="">All types</option>
                            <option>Book</option>
                            <option>Newspaper</option>
                            <option>Project</option>
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">
                            ▾
                        </span>
                    </div>
                </div>
            </section>

            {{-- DOCS LIST --}}
            <section class="mt-2">
                <div
                    class="bg-slate-900/80 border border-slate-500/60 rounded-xl-soft shadow-soft-glow p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2 mb-3">
                        <div>
                            <div class="text-sm sm:text-base font-semibold">Documents</div>
                            <div class="text-[11px] sm:text-xs text-slate-400">
                                Public view of your document library (published only).
                            </div>
                        </div>
                        <div class="text-[11px] sm:text-xs text-slate-400">
                            <span id="doc-count" class="font-medium text-slate-100">0</span> results
                        </div>
                    </div>

                    <div
                        id="docs-container"
                        class="max-h-[420px] overflow-y-auto pr-1.5 space-y-1">
                        {{-- Items rendered by JS --}}
                    </div>
                </div>
            </section>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="text-center text-[11px] sm:text-xs text-slate-500 px-4 py-6">
        Document Library – Public UI preview · <span id="year"></span>
    </footer>
</div>

{{-- JS (same logic, Tailwind just handles styling) --}}
<script>
    // Fake dataset for preview
    const documents = [
        {
            id: 1,
            title: "Introduction to Algorithms",
            author: "Thomas H. Cormen",
            field: "Computer Science",
            genre: "Book",
            year: 2022,
            status: "published",
            keywords: "algorithms, data structures",
        },
        {
            id: 2,
            title: "Deep Learning for Vision",
            author: "Jane Nguyen",
            field: "Computer Science",
            genre: "Project",
            year: 2023,
            status: "draft",
            keywords: "deep learning, CNN",
        },
        {
            id: 3,
            title: "Business Strategy Quarterly",
            author: "Global Insights",
            field: "Business",
            genre: "Newspaper",
            year: 2024,
            status: "published",
            keywords: "strategy, management",
        },
        {
            id: 4,
            title: "Civil Engineering Handbook",
            author: "Alan Smith",
            field: "Engineering",
            genre: "Book",
            year: 2019,
            status: "archived",
            keywords: "construction, materials",
        },
        {
            id: 5,
            title: "Machine Learning in Finance",
            author: "Sophie Tran",
            field: "Business",
            genre: "Project",
            year: 2021,
            status: "published",
            keywords: "finance, AI",
        },
    ];

    const docsContainer = document.getElementById("docs-container");
    const docCount = document.getElementById("doc-count");
    const searchInput = document.getElementById("search-input");
    const filterField = document.getElementById("filter-field");
    const filterGenre = document.getElementById("filter-genre");

    function renderDocs() {
        const q = searchInput.value.trim().toLowerCase();
        const field = filterField.value;
        const genre = filterGenre.value;

        const filtered = documents.filter((doc) => {
            if (doc.status !== "published") return false;
            if (field && doc.field !== field) return false;
            if (genre && doc.genre !== genre) return false;

            if (!q) return true;

            const haystack = (
                doc.title +
                " " +
                doc.author +
                " " +
                doc.field +
                " " +
                doc.genre +
                " " +
                doc.keywords
            ).toLowerCase();

            return haystack.includes(q);
        });

        docsContainer.innerHTML = "";
        filtered.forEach((doc) => {
            const wrapper = document.createElement("div");
            wrapper.className =
                "flex justify-between gap-4 py-3 px-3 rounded-2xl border border-transparent hover:border-indigo-400/70 hover:bg-slate-900/80 hover:-translate-y-px transition cursor-pointer";
            wrapper.innerHTML = `
                <div class="min-w-0">
                    <div class="text-sm font-semibold truncate">${doc.title}</div>
                    <div class="mt-0.5 text-[11px] text-slate-400">
                        ${doc.author} • ${doc.field} • ${doc.genre} • ${doc.year}
                    </div>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center rounded-full border border-indigo-400/80 bg-indigo-500/70 px-2 py-[3px] text-[10px] font-medium text-slate-50">
                            ${doc.field}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-slate-500/70 bg-slate-950/80 px-2 py-[3px] text-[10px] text-slate-100">
                            ${doc.genre}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-slate-500/70 bg-slate-950/80 px-2 py-[3px] text-[10px] text-slate-100">
                            Keywords: ${doc.keywords}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1 text-[10px] sm:text-[11px] whitespace-nowrap">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-full border border-slate-500/80 bg-slate-950/90 px-2.5 py-[3px] text-[10px] text-slate-100 hover:bg-slate-900 transition">
                        View details
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-full border border-transparent bg-gradient-to-r from-indigo-500 to-emerald-500 px-2.5 py-[3px] text-[10px] font-medium text-white shadow-md shadow-cyan-400/50 hover:shadow-cyan-400/70 transition">
                        Download
                    </button>
                </div>
            `;
            docsContainer.appendChild(wrapper);
        });

        docCount.textContent = filtered.length;
    }

    [searchInput, filterField, filterGenre].forEach((el) => {
        el.addEventListener("input", renderDocs);
        el.addEventListener("change", renderDocs);
    });

    renderDocs();
    document.getElementById("year").textContent = new Date().getFullYear();
</script>
</body>
</html>
