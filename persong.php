<?php
// File: realistic_walking_person.php
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Realistic 3D Walking Person</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        body { margin: 0; overflow: hidden; }
        canvas { display: block; }
        #controls {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255,255,255,0.7);
            padding: 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }
        #controls label {
            display: block;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div id="controls">
        <button id="toggleWalk">Toggle Walk</button>
        <label>Speed: <input type="range" id="speed" min="0.1" max="2" step="0.1" value="1"></label>
        <label>Arm Swing: <input type="range" id="armSwing" min="0.1" max="1.5" step="0.1" value="0.8"></label>
        <label>Leg Swing: <input type="range" id="legSwing" min="0.1" max="1.5" step="0.1" value="0.6"></label>
    </div>

    <script>
        // Initial parameters
        const params = {
            walkSpeed: <?php echo json_encode(1.0); ?>,
            armSwing: <?php echo json_encode(0.8); ?>,
            legSwing: <?php echo json_encode(0.6); ?>,
            bodyColor: <?php echo json_encode('#4682B4'); ?>,
            headColor: <?php echo json_encode('#FFD700'); ?>,
            limbColor: <?php echo json_encode('#8B4513'); ?>,
            shirtColor: <?php echo json_encode('#4169E1'); ?>
        };

        // Three.js setup
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x87CEEB);
        
        // Add ground with texture
        const groundGeometry = new THREE.PlaneGeometry(20, 100);
        const groundMaterial = new THREE.MeshStandardMaterial({ 
            color: 0x228B22,
            roughness: 0.8,
            metalness: 0.2
        });
        const ground = new THREE.Mesh(groundGeometry, groundMaterial);
        ground.rotation.x = -Math.PI / 2;
        ground.receiveShadow = true;
        scene.add(ground);

        // Add grid helper
        const gridHelper = new THREE.GridHelper(20, 20);
        scene.add(gridHelper);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0x404040);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(5, 10, 7);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        directionalLight.shadow.camera.near = 0.5;
        directionalLight.shadow.camera.far = 50;
        scene.add(directionalLight);

        // Camera setup
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(0, 3, 8);
        camera.lookAt(0, 1, 0);

        // Renderer
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        document.body.appendChild(renderer.domElement);

        // Create a realistic person
        const person = new THREE.Group();
        person.position.set(0, 0, 0);
        
        // Head with face details
        const headGeometry = new THREE.SphereGeometry(0.22, 32, 32);
        const headMaterial = new THREE.MeshStandardMaterial({ 
            color: new THREE.Color(params.headColor),
            roughness: 0.4,
            metalness: 0.1
        });
        const head = new THREE.Mesh(headGeometry, headMaterial);
        head.position.y = 1.7;
        head.castShadow = true;
        person.add(head);
        
        // Neck
        const neckGeometry = new THREE.CylinderGeometry(0.08, 0.1, 0.15, 8);
        const neckMaterial = new THREE.MeshStandardMaterial({ 
            color: new THREE.Color(params.headColor),
            roughness: 0.4
        });
        const neck = new THREE.Mesh(neckGeometry, neckMaterial);
        neck.position.y = 1.55;
        neck.castShadow = true;
        person.add(neck);
        
        // Torso (with shirt)
        const torsoGeometry = new THREE.BoxGeometry(0.5, 0.7, 0.3);
        const torsoMaterial = new THREE.MeshStandardMaterial({ 
            color: new THREE.Color(params.shirtColor),
            roughness: 0.6
        });
        const torso = new THREE.Mesh(torsoGeometry, torsoMaterial);
        torso.position.y = 1.1;
        torso.castShadow = true;
        person.add(torso);
        
        // Arms with upper/lower parts
        const upperArmGeometry = new THREE.CylinderGeometry(0.1, 0.1, 0.5, 8);
        const lowerArmGeometry = new THREE.CylinderGeometry(0.08, 0.1, 0.4, 8);
        const armMaterial = new THREE.MeshStandardMaterial({ 
            color: new THREE.Color(params.limbColor),
            roughness: 0.5
        });
        
        // Left arm
        const leftUpperArm = new THREE.Mesh(upperArmGeometry, armMaterial);
        leftUpperArm.position.set(-0.3, 1.3, 0);
        leftUpperArm.rotation.z = Math.PI / 2;
        leftUpperArm.castShadow = true;
        
        const leftLowerArm = new THREE.Mesh(lowerArmGeometry, armMaterial);
        leftLowerArm.position.set(-0.55, 1.3, 0);
        leftLowerArm.rotation.z = Math.PI / 2;
        leftLowerArm.castShadow = true;
        
        person.add(leftUpperArm);
        person.add(leftLowerArm);
        
        // Right arm
        const rightUpperArm = new THREE.Mesh(upperArmGeometry, armMaterial);
        rightUpperArm.position.set(0.3, 1.3, 0);
        rightUpperArm.rotation.z = Math.PI / 2;
        rightUpperArm.castShadow = true;
        
        const rightLowerArm = new THREE.Mesh(lowerArmGeometry, armMaterial);
        rightLowerArm.position.set(0.55, 1.3, 0);
        rightLowerArm.rotation.z = Math.PI / 2;
        rightLowerArm.castShadow = true;
        
        person.add(rightUpperArm);
        person.add(rightLowerArm);
        
        // Legs with upper/lower parts
        const upperLegGeometry = new THREE.CylinderGeometry(0.15, 0.15, 0.6, 8);
        const lowerLegGeometry = new THREE.CylinderGeometry(0.12, 0.15, 0.6, 8);
        const legMaterial = new THREE.MeshStandardMaterial({ 
            color: new THREE.Color('#1E90FF'),
            roughness: 0.5
        });
        
        // Left leg
        const leftUpperLeg = new THREE.Mesh(upperLegGeometry, legMaterial);
        leftUpperLeg.position.set(-0.15, 0.7, 0);
        leftUpperLeg.castShadow = true;
        
        const leftLowerLeg = new THREE.Mesh(lowerLegGeometry, legMaterial);
        leftLowerLeg.position.set(-0.15, 0.3, 0);
        leftLowerLeg.castShadow = true;
        
        person.add(leftUpperLeg);
        person.add(leftLowerLeg);
        
        // Right leg
        const rightUpperLeg = new THREE.Mesh(upperLegGeometry, legMaterial);
        rightUpperLeg.position.set(0.15, 0.7, 0);
        rightUpperLeg.castShadow = true;
        
        const rightLowerLeg = new THREE.Mesh(lowerLegGeometry, legMaterial);
        rightLowerLeg.position.set(0.15, 0.3, 0);
        rightLowerLeg.castShadow = true;
        
        person.add(rightUpperLeg);
        person.add(rightLowerLeg);
        
        // Feet
        const footGeometry = new THREE.BoxGeometry(0.2, 0.1, 0.3);
        const footMaterial = new THREE.MeshStandardMaterial({ 
            color: new THREE.Color('#000000'),
            roughness: 0.8
        });
        
        const leftFoot = new THREE.Mesh(footGeometry, footMaterial);
        leftFoot.position.set(-0.15, 0.05, 0.1);
        leftFoot.castShadow = true;
        person.add(leftFoot);
        
        const rightFoot = new THREE.Mesh(footGeometry, footMaterial);
        rightFoot.position.set(0.15, 0.05, 0.1);
        rightFoot.castShadow = true;
        person.add(rightFoot);
        
        scene.add(person);

        // Animation state
        let isWalking = true;
        let animationSpeed = params.walkSpeed;
        let armSwing = params.armSwing;
        let legSwing = params.legSwing;
        let time = 0;
        
        // Controls
        document.getElementById('toggleWalk').addEventListener('click', () => {
            isWalking = !isWalking;
        });
        
        document.getElementById('speed').addEventListener('input', (e) => {
            animationSpeed = parseFloat(e.target.value);
        });
        
        document.getElementById('armSwing').addEventListener('input', (e) => {
            armSwing = parseFloat(e.target.value);
        });
        
        document.getElementById('legSwing').addEventListener('input', (e) => {
            legSwing = parseFloat(e.target.value);
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            
            if (isWalking) {
                time += 0.01 * animationSpeed;
                
                // Natural walking motion
                const walkCycle = Math.sin(time * 2);
                const oppositeCycle = Math.sin(time * 2 + Math.PI);
                
                // Arm movement (opposite to legs)
                leftUpperArm.rotation.z = Math.PI/2 + walkCycle * armSwing * 0.5;
                rightUpperArm.rotation.z = Math.PI/2 + oppositeCycle * armSwing * 0.5;
                
                // Elbow bend
                leftLowerArm.rotation.z = walkCycle * armSwing * 0.3;
                rightLowerArm.rotation.z = oppositeCycle * armSwing * 0.3;
                
                // Leg movement
                leftUpperLeg.rotation.x = -walkCycle * legSwing * 0.5;
                rightUpperLeg.rotation.x = -oppositeCycle * legSwing * 0.5;
                
                // Knee bend
                leftLowerLeg.rotation.x = Math.max(0, walkCycle * legSwing * 0.3);
                rightLowerLeg.rotation.x = Math.max(0, oppositeCycle * legSwing * 0.3);
                
                // Foot angle
                leftFoot.rotation.x = -walkCycle * legSwing * 0.2;
                rightFoot.rotation.x = -oppositeCycle * legSwing * 0.2;
                
                // Slight body bounce and rotation for realism
                person.position.y = 0.05 * Math.abs(walkCycle);
                torso.rotation.x = 0.05 * walkCycle;
                torso.rotation.z = 0.02 * walkCycle;
                
                // Head stabilization (slight opposite movement)
                head.rotation.x = -0.03 * walkCycle;
                head.rotation.z = -0.01 * walkCycle;
                
                // Move forward
                person.position.z -= 0.02 * animationSpeed;
                
                // Camera follows the person
                camera.position.z = 8 + person.position.z;
                camera.lookAt(person.position.x, person.position.y + 1.5, person.position.z);
                
                // Reset position if gone too far
                if (person.position.z < -80) {
                    person.position.z = 0;
                }
            }
            
            renderer.render(scene, camera);
        }
        animate();
    </script>
</body>
</html>