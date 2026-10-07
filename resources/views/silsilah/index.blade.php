@extends('layouts.app')

@section('title', 'Pohon Silsilah - ' . ($kambing ? $kambing->nama : 'Kambing'))

@section('content')
<div class="space-y-4">
    <!-- Header & Selector Card -->
    <x-section-card>
        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-lg shadow-2xs">
                    <i class="ti ti-sitemap"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 tracking-tight leading-tight">
                        Pohon Silsilah Visual:
                        <span id="title-kambing-nama" class="text-emerald-700">{{ $kambing ? $kambing->nama : 'Belum Ada Kambing' }}</span>
                    </h1>
                    <p class="text-[11px] text-slate-500">
                        Geser (pan) & Pinch/Scroll (zoom) untuk eksplorasi.
                    </p>
                </div>
            </div>

            @if ($allKambings->isNotEmpty())
                <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                    <select id="select-kambing"
                        class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach ($allKambings as $k)
                            <option value="{{ $k->id }}" {{ $kambing && $kambing->id === $k->id ? 'selected' : '' }}>
                                {{ $k->jenis_kelamin === 'jantan' ? '♂' : '♀' }} {{ $k->nama }} {{ $k->ras ? '(' . $k->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <a id="btn-lihat-profil" href="{{ $kambing ? route('kambings.show', $kambing) : '#' }}"
                        class="px-3 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl border border-slate-200 transition whitespace-nowrap active:scale-95">
                        Profil &rarr;
                    </a>
                </div>
            @endif
        </div>
    </x-section-card>

    @if (!$kambing)
        <x-empty-state
            title="Belum ada data kambing"
            description="Tambahkan kambing terlebih dahulu untuk melihat visualisasi pohon silsilah."
            icon="ti ti-sitemap"
            actionText="+ Tambah Kambing"
            actionHref="{{ route('kambings.create') }}"
        />
    @else
        <!-- Canvas Viewport -->
        <div class="relative bg-slate-950 border border-slate-800 rounded-2xl shadow-inner overflow-hidden select-none"
             style="height: 600px;" id="canvas-viewport">

            <!-- Controls (Zoom In, Zoom Out, Reset) -->
            <div class="absolute top-3 right-3 z-20 flex flex-col gap-1.5 bg-slate-900/90 backdrop-blur border border-slate-700/80 rounded-2xl p-1 shadow-lg">
                <button type="button" id="btn-zoom-in" title="Zoom In"
                    class="w-10 h-10 flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800 rounded-xl font-bold text-lg transition active:scale-95">
                    +
                </button>
                <button type="button" id="btn-zoom-out" title="Zoom Out"
                    class="w-10 h-10 flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800 rounded-xl font-bold text-lg transition active:scale-95">
                    &minus;
                </button>
                <button type="button" id="btn-zoom-reset" title="Reset Tampilan"
                    class="w-10 h-10 flex items-center justify-center text-slate-200 hover:text-white hover:bg-slate-800 rounded-xl text-xs font-bold transition active:scale-95">
                    ↺
                </button>
            </div>

            <!-- Legend Badge -->
            <div class="absolute bottom-3 left-3 z-20 flex flex-wrap items-center gap-2.5 bg-slate-900/90 backdrop-blur border border-slate-700/80 rounded-xl px-3 py-1.5 text-[11px] text-slate-300 shadow-lg">
                <div class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span>Jantan</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                    <span>Betina</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-md bg-emerald-500 ring-2 ring-emerald-300"></span>
                    <span class="font-bold text-emerald-300">Pusat</span>
                </div>
            </div>

            <!-- Pan-Zoom Tree Surface -->
            <div id="tree-surface" class="absolute cursor-grab active:cursor-grabbing origin-top-left"
                 style="width: 3200px; height: 1800px; transform: translate(0px, 0px) scale(1);">
                <!-- SVG Connector Lines Layer -->
                <svg id="tree-svg-lines" class="absolute inset-0 pointer-events-none" width="3200" height="1800" style="overflow: visible;"></svg>

                <!-- HTML Nodes Layer -->
                <div id="tree-nodes-container" class="absolute inset-0 pointer-events-auto"></div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Detail Node Kambing -->
<div id="modal-node-detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white border border-slate-200 rounded-3xl shadow-2xl max-w-xs w-full p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-start justify-between pb-2 border-b border-slate-100">
            <div>
                <span id="modal-role-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700 uppercase tracking-wider block w-fit mb-1">
                    Kambing
                </span>
                <h3 id="modal-kambing-nama" class="text-lg font-extrabold text-slate-900">Nama</h3>
            </div>
            <button type="button" id="btn-close-modal" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 active:scale-95 transition">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <div id="modal-kambing-avatar" class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center text-2xl">
                🐐
            </div>
            <div class="space-y-0.5 text-xs text-slate-600">
                <p>Kelamin: <strong id="modal-kambing-gender" class="text-slate-900">-</strong></p>
                <p>Ras: <span id="modal-kambing-ras" class="text-slate-800 font-semibold">-</span></p>
                <p>Lahir: <span id="modal-kambing-lahir" class="text-slate-800 font-semibold">-</span></p>
                <p>Status: <span id="modal-kambing-status" class="capitalize font-bold">-</span></p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <a id="modal-btn-detail" href="#" class="px-3 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">
                Profil &rarr;
            </a>
            <button type="button" id="modal-btn-set-pusat"
                class="px-3 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-xs transition active:scale-95">
                🌳 Pusat
            </button>
        </div>
    </div>
</div>

@if ($kambing)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const initialData = @json($silsilahData);
        let currentTreeData = initialData;
        let currentPusatId = {{ $kambing->id }};

        const viewport = document.getElementById('canvas-viewport');
        const surface = document.getElementById('tree-surface');
        const svgLines = document.getElementById('tree-svg-lines');
        const nodesContainer = document.getElementById('tree-nodes-container');

        const btnZoomIn = document.getElementById('btn-zoom-in');
        const btnZoomOut = document.getElementById('btn-zoom-out');
        const btnZoomReset = document.getElementById('btn-zoom-reset');
        const selectKambing = document.getElementById('select-kambing');
        const titleNama = document.getElementById('title-kambing-nama');
        const btnLihatProfil = document.getElementById('btn-lihat-profil');

        // Modal Elements
        const modal = document.getElementById('modal-node-detail');
        const btnCloseModal = document.getElementById('btn-close-modal');
        const modalNama = document.getElementById('modal-kambing-nama');
        const modalRole = document.getElementById('modal-role-badge');
        const modalAvatar = document.getElementById('modal-kambing-avatar');
        const modalGender = document.getElementById('modal-kambing-gender');
        const modalRas = document.getElementById('modal-kambing-ras');
        const modalLahir = document.getElementById('modal-kambing-lahir');
        const modalStatus = document.getElementById('modal-kambing-status');
        const modalBtnDetail = document.getElementById('modal-btn-detail');
        const modalBtnSetPusat = document.getElementById('modal-btn-set-pusat');

        let selectedNodeKambing = null;

        // Viewport Transform State
        let scale = 0.95;
        let panX = 0;
        let panY = 0;
        let isDragging = false;
        let startX = 0;
        let startY = 0;

        function updateTransform() {
            surface.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
        }

        // Panning Handlers
        viewport.addEventListener('mousedown', function (e) {
            if (e.target.closest('.interactive-node') || e.target.closest('button') || e.target.closest('select')) {
                return;
            }
            isDragging = true;
            startX = e.clientX - panX;
            startY = e.clientY - panY;
        });

        window.addEventListener('mousemove', function (e) {
            if (!isDragging) return;
            panX = e.clientX - startX;
            panY = e.clientY - startY;
            updateTransform();
        });

        window.addEventListener('mouseup', function () {
            isDragging = false;
        });

        // Touch Panning for Mobile
        viewport.addEventListener('touchstart', function (e) {
            if (e.target.closest('.interactive-node') || e.target.closest('button') || e.target.closest('select')) {
                return;
            }
            if (e.touches.length === 1) {
                isDragging = true;
                startX = e.touches[0].clientX - panX;
                startY = e.touches[0].clientY - panY;
            }
        }, { passive: true });

        window.addEventListener('touchmove', function (e) {
            if (!isDragging || e.touches.length !== 1) return;
            panX = e.touches[0].clientX - startX;
            panY = e.touches[0].clientY - startY;
            updateTransform();
        }, { passive: true });

        window.addEventListener('touchend', function () {
            isDragging = false;
        });

        // Zoom Handlers
        viewport.addEventListener('wheel', function (e) {
            e.preventDefault();
            const zoomFactor = e.deltaY < 0 ? 1.1 : 0.9;
            const newScale = Math.min(Math.max(scale * zoomFactor, 0.35), 2.2);

            const rect = viewport.getBoundingClientRect();
            const cx = rect.width / 2;
            const cy = rect.height / 2;

            panX = cx - (cx - panX) * (newScale / scale);
            panY = cy - (cy - panY) * (newScale / scale);
            scale = newScale;

            updateTransform();
        }, { passive: false });

        btnZoomIn.addEventListener('click', function () {
            scale = Math.min(scale * 1.2, 2.2);
            updateTransform();
        });

        btnZoomOut.addEventListener('click', function () {
            scale = Math.max(scale / 1.2, 0.35);
            updateTransform();
        });

        btnZoomReset.addEventListener('click', function () {
            centerOnPusat();
        });

        function centerOnPusat() {
            scale = 0.95;
            const rect = viewport.getBoundingClientRect();
            panX = (rect.width / 2) - (1600 * scale);
            panY = (rect.height / 2) - (580 * scale);
            updateTransform();
        }

        // Tree Renderer
        const NODE_WIDTH = 190;
        const NODE_HEIGHT = 86;

        function renderTree(data) {
            nodesContainer.innerHTML = '';
            svgLines.innerHTML = '';

            if (!data || !data.pusat) return;

            const renderedNodes = {};

            function addNodeCard(node, x, y, customRole = '') {
                if (!node) return null;
                const isPusat = node.role === 'pusat' || customRole === 'pusat';
                const isJantan = node.jenis_kelamin === 'jantan';

                let borderClass = isJantan ? 'border-blue-400 bg-blue-950/80 text-blue-100' : 'border-pink-400 bg-pink-950/80 text-pink-100';
                if (isPusat) {
                    borderClass = 'border-emerald-400 bg-emerald-950/90 text-emerald-100 ring-4 ring-emerald-500/50 shadow-2xl';
                }

                const card = document.createElement('div');
                card.className = `interactive-node absolute rounded-xl border-2 p-2.5 cursor-pointer transition-transform hover:scale-105 shadow-md flex items-center gap-2.5 ${borderClass}`;
                card.style.width = `${NODE_WIDTH}px`;
                card.style.height = `${NODE_HEIGHT}px`;
                card.style.left = `${x - (NODE_WIDTH / 2)}px`;
                card.style.top = `${y - (NODE_HEIGHT / 2)}px`;

                const roleLabel = customRole || node.role;
                let roleBadge = '';
                if (isPusat) {
                    roleBadge = `<span class="bg-emerald-500 text-emerald-950 text-[10px] font-extrabold px-1.5 py-0.2 rounded uppercase">Pusat</span>`;
                } else if (roleLabel) {
                    roleBadge = `<span class="text-[10px] opacity-75 uppercase">${roleLabel.replace('_', ' ')}</span>`;
                }

                const imgHtml = node.foto_url
                    ? `<img src="${node.foto_url}" class="w-10 h-10 rounded-lg object-cover border border-white/20 flex-shrink-0">`
                    : `<div class="w-10 h-10 rounded-lg bg-black/30 border border-white/10 flex items-center justify-center text-lg flex-shrink-0">🐐</div>`;

                card.innerHTML = `
                    ${imgHtml}
                    <div class="overflow-hidden flex-1 leading-tight">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-xs font-semibold ${isJantan ? 'text-blue-300' : 'text-pink-300'}">${isJantan ? '♂ Jantan' : '♀ Betina'}</span>
                            ${roleBadge}
                        </div>
                        <h4 class="font-bold text-sm text-white truncate" title="${node.nama}">${node.nama}</h4>
                        <p class="text-[11px] opacity-75 truncate">${node.ras || '-'}</p>
                    </div>
                `;

                card.addEventListener('click', function (e) {
                    e.stopPropagation();
                    openModal(node, roleLabel);
                });

                nodesContainer.appendChild(card);

                const centerPos = { x, y, node };
                renderedNodes[node.id + '_' + roleLabel] = centerPos;
                return centerPos;
            }

            function drawCurve(fromX, fromY, toX, toY, color = '#64748b') {
                const midY = (fromY + toY) / 2;
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                const d = `M ${fromX} ${fromY} C ${fromX} ${midY}, ${toX} ${midY}, ${toX} ${toY}`;
                path.setAttribute('d', d);
                path.setAttribute('stroke', color);
                path.setAttribute('stroke-width', '2.5');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('fill', 'none');
                svgLines.appendChild(path);
            }

            const CENTER_X = 1600;

            const Y_GRANDPARENTS = 130;  // Gen -2
            const Y_PARENTS = 330;        // Gen -1
            const Y_PUSAT = 550;          // Gen 0
            const Y_CHILDREN = 770;       // Gen +1
            const Y_GRANDCHILDREN = 990;  // Gen +2

            // 1. Render Pusat
            const pusatPos = addNodeCard(data.pusat, CENTER_X, Y_PUSAT, 'pusat');

            // 2. Render Siblings
            const saudaraList = data.saudara || [];
            if (saudaraList.length > 0) {
                const spacing = 220;
                saudaraList.forEach((s, i) => {
                    const side = i % 2 === 0 ? 1 : -1;
                    const offset = Math.ceil((i + 1) / 2) * spacing * side;
                    const sX = CENTER_X + offset;
                    addNodeCard(s, sX, Y_PUSAT, 'saudara');
                });
            }

            // 3. Render Parents
            const ayah = data.orang_tua?.ayah;
            const ibu = data.orang_tua?.ibu;
            let ayahPos = null;
            let ibuPos = null;

            if (ayah) {
                ayahPos = addNodeCard(ayah, CENTER_X - 220, Y_PARENTS, 'ayah');
            }
            if (ibu) {
                ibuPos = addNodeCard(ibu, CENTER_X + 220, Y_PARENTS, 'ibu');
            }

            if (ayahPos || ibuPos) {
                const parentUnionX = ayahPos && ibuPos ? (ayahPos.x + ibuPos.x) / 2 : (ayahPos ? ayahPos.x : ibuPos.x);
                const parentUnionY = Y_PARENTS + (NODE_HEIGHT / 2);

                if (ayahPos && ibuPos) {
                    const hLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    hLine.setAttribute('x1', ayahPos.x + (NODE_WIDTH / 2));
                    hLine.setAttribute('y1', ayahPos.y);
                    hLine.setAttribute('x2', ibuPos.x - (NODE_WIDTH / 2));
                    hLine.setAttribute('y2', ibuPos.y);
                    hLine.setAttribute('stroke', '#ec4899');
                    hLine.setAttribute('stroke-width', '2');
                    svgLines.appendChild(hLine);
                }

                drawCurve(parentUnionX, parentUnionY, pusatPos.x, pusatPos.y - (NODE_HEIGHT / 2), '#a855f7');

                saudaraList.forEach((s, i) => {
                    const side = i % 2 === 0 ? 1 : -1;
                    const offset = Math.ceil((i + 1) / 2) * 220 * side;
                    const sX = CENTER_X + offset;
                    drawCurve(parentUnionX, parentUnionY, sX, Y_PUSAT - (NODE_HEIGHT / 2), '#94a3b8');
                });
            }

            // 4. Render Grandparents
            const gp = data.kakek_nenek || {};
            if (ayahPos) {
                const kakekAyah = gp.kakek_dari_ayah;
                const nenekAyah = gp.nenek_dari_ayah;
                if (kakekAyah) {
                    const kAPos = addNodeCard(kakekAyah, ayahPos.x - 110, Y_GRANDPARENTS, 'kakek');
                    drawCurve(kAPos.x, kAPos.y + (NODE_HEIGHT / 2), ayahPos.x, ayahPos.y - (NODE_HEIGHT / 2), '#3b82f6');
                }
                if (nenekAyah) {
                    const nAPos = addNodeCard(nenekAyah, ayahPos.x + 110, Y_GRANDPARENTS, 'nenek');
                    drawCurve(nAPos.x, nAPos.y + (NODE_HEIGHT / 2), ayahPos.x, ayahPos.y - (NODE_HEIGHT / 2), '#ec4899');
                }
            }

            if (ibuPos) {
                const kakekIbu = gp.kakek_dari_ibu;
                const nenekIbu = gp.nenek_dari_ibu;
                if (kakekIbu) {
                    const kIPos = addNodeCard(kakekIbu, ibuPos.x - 110, Y_GRANDPARENTS, 'kakek');
                    drawCurve(kIPos.x, kIPos.y + (NODE_HEIGHT / 2), ibuPos.x, ibuPos.y - (NODE_HEIGHT / 2), '#3b82f6');
                }
                if (nenekIbu) {
                    const nIPos = addNodeCard(nenekIbu, ibuPos.x + 110, Y_GRANDPARENTS, 'nenek');
                    drawCurve(nIPos.x, nIPos.y + (NODE_HEIGHT / 2), ibuPos.x, ibuPos.y - (NODE_HEIGHT / 2), '#ec4899');
                }
            }

            // 5. Render Children and Grandchildren
            const keturunanList = data.keturunan || [];
            if (keturunanList.length > 0) {
                const childCount = keturunanList.length;
                const totalWidth = (childCount - 1) * 240;
                const startChildX = CENTER_X - (totalWidth / 2);

                keturunanList.forEach((item, idx) => {
                    const child = item.anak;
                    const cX = startChildX + (idx * 240);
                    const childPos = addNodeCard(child, cX, Y_CHILDREN, 'anak');

                    drawCurve(pusatPos.x, pusatPos.y + (NODE_HEIGHT / 2), childPos.x, childPos.y - (NODE_HEIGHT / 2), '#10b981');

                    const cucuList = item.cucu || [];
                    if (cucuList.length > 0) {
                        const cucuCount = cucuList.length;
                        const cucuTotalWidth = (cucuCount - 1) * 200;
                        const startCucuX = cX - (cucuTotalWidth / 2);

                        cucuList.forEach((cucu, cIdx) => {
                            const cucuX = startCucuX + (cIdx * 200);
                            const cucuPos = addNodeCard(cucu, cucuX, Y_GRANDCHILDREN, 'cucu');
                            drawCurve(childPos.x, childPos.y + (NODE_HEIGHT / 2), cucuPos.x, cucuPos.y - (NODE_HEIGHT / 2), '#06b6d4');
                        });
                    }
                });
            }
        }

        // Modal Handlers
        function openModal(node, role) {
            selectedNodeKambing = node;
            modalNama.textContent = node.nama;
            modalRole.textContent = role ? role.replace('_', ' ') : 'Kambing';
            modalGender.textContent = node.jenis_kelamin === 'jantan' ? '♂ Jantan' : '♀ Betina';
            modalGender.className = node.jenis_kelamin === 'jantan' ? 'text-blue-600 font-bold' : 'text-pink-600 font-bold';
            modalRas.textContent = node.ras || '-';
            modalLahir.textContent = node.tanggal_lahir || '-';
            modalStatus.textContent = node.status || 'hidup';
            modalBtnDetail.href = node.detail_url;

            if (node.foto_url) {
                modalAvatar.innerHTML = `<img src="${node.foto_url}" class="w-full h-full object-cover">`;
            } else {
                modalAvatar.innerHTML = `🐐`;
            }

            if (node.id === currentPusatId) {
                modalBtnSetPusat.classList.add('hidden');
            } else {
                modalBtnSetPusat.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            selectedNodeKambing = null;
        }

        btnCloseModal.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        function setPusatKambing(kambingId) {
            closeModal();
            fetch(`/silsilah/${kambingId}/data`)
                .then(res => res.json())
                .then(newData => {
                    currentTreeData = newData;
                    currentPusatId = kambingId;

                    if (titleNama) titleNama.textContent = newData.pusat.nama;
                    if (btnLihatProfil) btnLihatProfil.href = newData.pusat.detail_url;
                    if (selectKambing) selectKambing.value = kambingId;

                    window.history.pushState({ id: kambingId }, '', `/silsilah/${kambingId}`);

                    renderTree(currentTreeData);
                    centerOnPusat();
                })
                .catch(err => {
                    console.error('Gagal memuat data silsilah:', err);
                    window.location.href = `/silsilah/${kambingId}`;
                });
        }

        modalBtnSetPusat.addEventListener('click', function () {
            if (selectedNodeKambing) {
                setPusatKambing(selectedNodeKambing.id);
            }
        });

        if (selectKambing) {
            selectKambing.addEventListener('change', function () {
                setPusatKambing(this.value);
            });
        }

        renderTree(currentTreeData);
        centerOnPusat();
    });
</script>
@endif
@endsection
