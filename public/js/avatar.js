/**
 * Colley — 3D izomcsoport-választó (Three.js r128).
 *
 * Procedurális, stilizált "papírbábu" test: minden interaktív izomcsoport egy vagy két
 * egyszerű dobozgeometria, a sablon adatai (pozíció, méret, elöl/hátul) a KOD alapján.
 * A kattintható mesh-ek userData.izomcsoportId-ja köti őket a valódi checkboxokhoz
 * (data-mesh = izomcsoport.mesh_nev) — a 3D jelenet csak be-/kipipálja a mezőket,
 * a kiválasztás egyetlen igazság-forrása a checkbox-lista marad.
 *
 * Kutatás alatt egy anatómiailag szegmentált, licencelt modellre váltás (lásd README) —
 * ez a procedurális változat addig a demonstrálható, önálló megoldás.
 */
(function () {
    'use strict';

    const adatElem = document.getElementById('izomcsoport-adat');
    const vaszon = document.getElementById('avatar-canvas');
    if (!adatElem || !vaszon || typeof THREE === 'undefined') {
        return;
    }
    const izmok = JSON.parse(adatElem.textContent);

    const SZIN_ALAP = 0x3f6b66;
    const SZIN_KIJELOLT = 0xe0a63e;
    const SZIN_SZALLVANY = 0x2a4349;

    // Elrendezés kódonként: elöl/hátul (z-eltolással, hogy forgatva mindkettő elérhető legyen),
    // doboz mérete [szélesség, magasság, mélység], pozíciója (egy közös, vagy [bal, jobb] pár).
    const ELRENDEZES = {
        NYAK: { z: 1, meret: [0.45, 0.35, 0.35], poz: [0, 3.5, 0.1] },
        VALL: { z: 1, meret: [0.5, 0.28, 0.45], poz: [[-0.9, 3.0, 0], [0.9, 3.0, 0]] },
        MELL: { z: 1, meret: [1.5, 0.55, 0.3], poz: [0, 2.5, 0.28] },
        BICEPSZ: { z: 1, meret: [0.3, 0.5, 0.3], poz: [[-1.2, 1.9, 0.15], [1.2, 1.9, 0.15]] },
        HAS: { z: 1, meret: [0.85, 0.5, 0.25], poz: [0, 1.8, 0.26] },
        FERDE_HAS: { z: 1, meret: [0.28, 0.5, 0.24], poz: [[-0.75, 1.8, 0.18], [0.75, 1.8, 0.18]] },
        ALKAR: { z: 1, meret: [0.26, 0.5, 0.26], poz: [[-1.25, 1.05, 0], [1.25, 1.05, 0]] },
        COMB_ELUL: { z: 1, meret: [0.42, 0.7, 0.32], poz: [[-0.42, 0.25, 0.12], [0.42, 0.25, 0.12]] },
        TRAPEZ: { z: -1, meret: [1.0, 0.3, 0.3], poz: [0, 3.05, -0.25] },
        HAT: { z: -1, meret: [1.5, 0.7, 0.3], poz: [0, 2.5, -0.28] },
        TRICEPSZ: { z: -1, meret: [0.3, 0.5, 0.3], poz: [[-1.2, 1.9, -0.15], [1.2, 1.9, -0.15]] },
        GERINC: { z: -1, meret: [0.5, 0.4, 0.22], poz: [0, 1.8, -0.26] },
        FARIZOM: { z: -1, meret: [0.95, 0.4, 0.3], poz: [0, 1.3, -0.2] },
        COMB_HATSO: { z: -1, meret: [0.38, 0.7, 0.28], poz: [[-0.42, 0.25, -0.12], [0.42, 0.25, -0.12]] },
        COMB_OLDAL: { z: -1, meret: [0.16, 0.7, 0.28], poz: [[-0.8, 0.25, 0], [0.8, 0.25, 0]] },
        VADLI: { z: -1, meret: [0.3, 0.65, 0.28], poz: [[-0.4, -0.7, 0], [0.4, -0.7, 0]] },
    };

    const jelenet = new THREE.Scene();
    const kamera = new THREE.PerspectiveCamera(42, vaszon.clientWidth / vaszon.clientHeight, 0.1, 100);
    kamera.position.set(0, 1.6, 6.2);

    const renderelo = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderelo.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderelo.setSize(vaszon.clientWidth, vaszon.clientHeight);
    vaszon.appendChild(renderelo.domElement);

    jelenet.add(new THREE.AmbientLight(0xffffff, 0.65));
    const fenyforras = new THREE.DirectionalLight(0xffffff, 0.9);
    fenyforras.position.set(3, 5, 4);
    jelenet.add(fenyforras);

    const test = new THREE.Group();
    jelenet.add(test);

    // Nem interaktív szállvány-elemek — csak a test formáját adják, nem izomcsoportok.
    const szallvanyAnyag = new THREE.MeshStandardMaterial({ color: SZIN_SZALLVANY });
    const fej = new THREE.Mesh(new THREE.SphereGeometry(0.33, 16, 16), szallvanyAnyag);
    fej.position.set(0, 3.95, 0);
    const medence = new THREE.Mesh(new THREE.BoxGeometry(0.9, 0.3, 0.5), szallvanyAnyag);
    medence.position.set(0, 0.9, 0);
    test.add(fej, medence);
    [-0.4, 0.4].forEach((x) => {
        const sip = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.14, 0.9, 10), szallvanyAnyag);
        sip.position.set(x, -1.3, 0);
        const lab = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.14, 0.5), szallvanyAnyag);
        lab.position.set(x, -1.83, 0.1);
        test.add(sip, lab);
    });

    const interaktivMeshek = [];

    function mezoLetrehoz(kod, meret, poz, zEltolas) {
        const anyag = new THREE.MeshStandardMaterial({ color: SZIN_ALAP });
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(meret[0], meret[1], meret[2]), anyag);
        mesh.position.set(poz[0], poz[1], poz[2] + zEltolas * 0.02);
        return mesh;
    }

    izmok.forEach((izom) => {
        const sablon = ELRENDEZES[izom.kod];
        if (!sablon) {
            return;
        }
        const egyPozicio = typeof sablon.poz[0] === 'number';
        const poziciok = egyPozicio ? [sablon.poz] : sablon.poz;

        poziciok.forEach((poz) => {
            const mesh = mezoLetrehoz(izom.kod, sablon.meret, poz, sablon.z);
            mesh.userData = { izomcsoportId: izom.izomcsoport_id, meshNev: izom.mesh_nev, nev: izom.nev };
            test.add(mesh);
            interaktivMeshek.push(mesh);
        });
    });

    function allitSzinek(izomcsoportId, kijelolt) {
        const szin = kijelolt ? SZIN_KIJELOLT : SZIN_ALAP;
        interaktivMeshek
            .filter((m) => m.userData.izomcsoportId === izomcsoportId)
            .forEach((m) => m.material.color.setHex(szin));
    }

    // Checkbox → 3D (mindkét irányból szinkronban, ha valaki a listában kattint).
    const checkboxok = document.querySelectorAll('#izom-lista input[type=checkbox]');
    checkboxok.forEach((cb) => {
        const id = Number(cb.value);
        allitSzinek(id, cb.checked);
        cb.addEventListener('change', () => allitSzinek(id, cb.checked));
    });

    // 3D → checkbox (kattintás, raycasttel — csak ha nem forgatásról van szó).
    const raycaster = new THREE.Raycaster();
    const mutato = new THREE.Vector2();
    let forgatva = false;
    let elozoX = 0;
    let elmozdulasOssz = 0;
    let autoForgas = true;
    let autoForgasUjraido = null;

    function kattintasKezeles(clientX, clientY) {
        const hatarok = vaszon.getBoundingClientRect();
        mutato.x = ((clientX - hatarok.left) / hatarok.width) * 2 - 1;
        mutato.y = -((clientY - hatarok.top) / hatarok.height) * 2 + 1;
        raycaster.setFromCamera(mutato, kamera);
        const talalatok = raycaster.intersectObjects(interaktivMeshek);
        if (talalatok.length === 0) {
            return;
        }
        const { izomcsoportId } = talalatok[0].object.userData;
        const cb = document.querySelector(`#izom-lista input[value="${izomcsoportId}"]`);
        if (cb) {
            cb.checked = !cb.checked;
            allitSzinek(izomcsoportId, cb.checked);
        }
    }

    function forgatasSzunet() {
        autoForgas = false;
        clearTimeout(autoForgasUjraido);
        autoForgasUjraido = setTimeout(() => { autoForgas = true; }, 1800);
    }

    vaszon.addEventListener('pointerdown', (e) => {
        forgatva = true;
        elozoX = e.clientX;
        elmozdulasOssz = 0;
        forgatasSzunet();
    });
    window.addEventListener('pointermove', (e) => {
        if (!forgatva) {
            return;
        }
        const delta = e.clientX - elozoX;
        test.rotation.y += delta * 0.008;
        elozoX = e.clientX;
        elmozdulasOssz += Math.abs(delta);
    });
    window.addEventListener('pointerup', (e) => {
        if (!forgatva) {
            return;
        }
        forgatva = false;
        if (elmozdulasOssz < 4) {
            kattintasKezeles(e.clientX, e.clientY);
        }
    });

    window.addEventListener('resize', () => {
        kamera.aspect = vaszon.clientWidth / vaszon.clientHeight;
        kamera.updateProjectionMatrix();
        renderelo.setSize(vaszon.clientWidth, vaszon.clientHeight);
    });

    (function animal() {
        requestAnimationFrame(animal);
        if (autoForgas) {
            test.rotation.y += 0.0025;
        }
        renderelo.render(jelenet, kamera);
    })();
})();
