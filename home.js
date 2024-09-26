// Inicializar la escena, la cámara y el renderizador
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x000000);

const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
camera.position.z = 50;

const renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

// Crear las estrellas (partículas)
function createStars() {
    const starGeometry = new THREE.BufferGeometry();
    const starMaterial = new THREE.PointsMaterial({ color: 0xffffff });
    const starVertices = [];

    for (let i = 0; i < 10000; i++) {
        const x = (Math.random() - 0.5) * 2000;
        const y = (Math.random() - 0.5) * 2000;
        const z = (Math.random() - 0.5) * 2000;
        starVertices.push(x, y, z);
    }

    starGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));

    const stars = new THREE.Points(starGeometry, starMaterial);
    scene.add(stars);
}

createStars();

// Crear la galaxia (torus con partículas girando)
const galaxyGeometry = new THREE.TorusGeometry(20, 5, 16, 100);
const galaxyMaterial = new THREE.MeshBasicMaterial({ color: 0x1abc9c, wireframe: true });
const galaxy = new THREE.Mesh(galaxyGeometry, galaxyMaterial);
scene.add(galaxy);

// Agujero negro (esfera oscura)
const blackHoleGeometry = new THREE.SphereGeometry(5, 32, 32);
const blackHoleMaterial = new THREE.MeshBasicMaterial({ color: 0x000000 });
const blackHole = new THREE.Mesh(blackHoleGeometry, blackHoleMaterial);
blackHole.position.set(0, 0, 0);
scene.add(blackHole);

// Crear la animación de absorción
function animate() {
    requestAnimationFrame(animate);

    // Rotar la galaxia
    galaxy.rotation.x += 0.01;
    galaxy.rotation.y += 0.01;

    // Simular atracción de la galaxia hacia el agujero negro
    galaxy.position.x -= 0.1;
    galaxy.position.y -= 0.1;

    // Rotar las estrellas lentamente
    scene.rotation.x += 0.001;
    scene.rotation.y += 0.002;

    renderer.render(scene, camera);
}

animate();

// Ajustar el tamaño del renderizado cuando se cambia el tamaño de la ventana
window.addEventListener('resize', () => {
    const width = window.innerWidth;
    const height = window.innerHeight;
    renderer.setSize(width, height);
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
});
