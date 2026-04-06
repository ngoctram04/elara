import { FaceLandmarker, FilesetResolver } from "@mediapipe/tasks-vision";

document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("open-try-lipstick");
    const closeBtn = document.getElementById("close-try-lipstick");
    const modal = document.getElementById("try-lipstick-modal");
    const overlay = document.getElementById("try-lipstick-overlay");
    const previewImage = document.getElementById("try-lipstick-image");
    const canvas = document.getElementById("try-lipstick-canvas");
    const label = document.getElementById("try-lipstick-label");
    const hint = document.getElementById("try-lipstick-hint");
    const colorButtons = document.querySelectorAll(".lip-color-btn");
    const uploadInput = document.getElementById("try-lipstick-upload");
    const opacityRange = document.getElementById("lip-opacity-range");

    if (!openBtn || !modal || !previewImage || !canvas || colorButtons.length === 0) {
        return;
    }

    const ctx = canvas.getContext("2d");
    let faceLandmarker = null;
    let currentColor = colorButtons[0]?.dataset.color || "#d94b70";
    let currentOpacity = Number(opacityRange?.value || 55) / 100;
    let currentLandmarks = null;
    let currentImageLoaded = false;

    const OUTER_LIP = [
        61, 146, 91, 181, 84, 17, 314, 405, 321, 375, 291,
        409, 270, 269, 267, 0, 37, 39, 40, 185
    ];

    const INNER_LIP = [
        78, 95, 88, 178, 87, 14, 317, 402, 318, 324, 308,
        415, 310, 311, 312, 13, 82, 81, 80, 191
    ];

    async function createFaceLandmarker() {
        if (faceLandmarker) return faceLandmarker;

        const vision = await FilesetResolver.forVisionTasks(
            "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm"
        );

        faceLandmarker = await FaceLandmarker.createFromOptions(vision, {
            baseOptions: {
                modelAssetPath:
                    "https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task",
                delegate: "GPU",
            },
            runningMode: "IMAGE",
            numFaces: 1,
            outputFaceBlendshapes: false,
            outputFacialTransformationMatrixes: false,
            minFaceDetectionConfidence: 0.5,
            minFacePresenceConfidence: 0.5,
            minTrackingConfidence: 0.5,
        });

        return faceLandmarker;
    }

    function openModal() {
        modal.style.display = "flex";
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        modal.style.display = "none";
        document.body.style.overflow = "";
    }

    function waitForImageRender() {
        return new Promise((resolve) => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    resolve();
                });
            });
        });
    }

    function resizeCanvasToImage() {
        const rect = previewImage.getBoundingClientRect();

        canvas.width = Math.round(rect.width);
        canvas.height = Math.round(rect.height);

        canvas.style.width = `${rect.width}px`;
        canvas.style.height = `${rect.height}px`;
    }

    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function pointFromLandmark(landmark) {
        return {
            x: landmark.x * canvas.width,
            y: landmark.y * canvas.height,
        };
    }

    function drawClosedPath(indices, landmarks) {
        if (!indices.length) return;

        const first = pointFromLandmark(landmarks[indices[0]]);
        ctx.moveTo(first.x, first.y);

        for (let i = 1; i < indices.length; i++) {
            const p = pointFromLandmark(landmarks[indices[i]]);
            ctx.lineTo(p.x, p.y);
        }

        ctx.closePath();
    }

    function drawLipMask(landmarks) {
        if (!landmarks || !canvas.width || !canvas.height) return;

        clearCanvas();

        ctx.save();

        ctx.globalAlpha = currentOpacity;
        ctx.fillStyle = currentColor;
        ctx.filter = "blur(1.2px)";

        // Tô phần môi giữa outer và inner
        ctx.beginPath();
        drawClosedPath(OUTER_LIP, landmarks);
        drawClosedPath([...INNER_LIP].reverse(), landmarks);
        ctx.fill("evenodd");

        // Viền ngoài nhẹ
        ctx.filter = "none";
        ctx.globalAlpha = Math.min(currentOpacity + 0.08, 0.9);
        ctx.lineWidth = 0.8;
        ctx.strokeStyle = currentColor;

        ctx.beginPath();
        drawClosedPath(OUTER_LIP, landmarks);
        ctx.stroke();

        ctx.restore();
    }

    async function detectAndRenderLips() {
        if (!currentImageLoaded) return;

        await waitForImageRender();
        resizeCanvasToImage();
        clearCanvas();

        try {
            const detector = await createFaceLandmarker();
            const result = detector.detect(previewImage);

            if (!result.faceLandmarks || result.faceLandmarks.length === 0) {
                currentLandmarks = null;
                clearCanvas();

                if (hint) {
                    hint.textContent = "Không nhận diện được khuôn mặt. Hãy thử ảnh rõ mặt hơn.";
                }
                return;
            }

            currentLandmarks = result.faceLandmarks[0];
            drawLipMask(currentLandmarks);

            if (hint) {
                hint.textContent = "Đã nhận diện môi. Bạn có thể đổi màu son bên dưới.";
            }
        } catch (error) {
            console.error("Lip detection error:", error);

            if (hint) {
                hint.textContent = "Không thể tải mô hình nhận diện môi.";
            }
        }
    }

    function setActiveColor(button) {
        currentColor = button.dataset.color || "#d94b70";

        colorButtons.forEach((btn) => btn.classList.remove("active"));
        button.classList.add("active");

        if (label) {
            label.textContent = button.getAttribute("title") || "";
        }

        if (currentLandmarks) {
            drawLipMask(currentLandmarks);
        }
    }

    function handleOpacityChange() {
        currentOpacity = Number(opacityRange.value || 55) / 100;

        if (currentLandmarks) {
            drawLipMask(currentLandmarks);
        }
    }

    function loadUploadedPreview(file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            previewImage.onload = async function () {
                currentImageLoaded = true;
                currentLandmarks = null;

                if (hint) {
                    hint.textContent = "Đang nhận diện môi...";
                }

                await detectAndRenderLips();
            };

            previewImage.src = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    openBtn.addEventListener("click", async function () {
        openModal();

        if (!currentImageLoaded && previewImage.complete && previewImage.naturalWidth > 0) {
            currentImageLoaded = true;
        }

        if (currentImageLoaded) {
            await detectAndRenderLips();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", closeModal);
    }

    if (overlay) {
        overlay.addEventListener("click", closeModal);
    }

    colorButtons.forEach((button) => {
        button.style.backgroundColor = button.dataset.color || "#d94b70";

        button.addEventListener("click", function () {
            setActiveColor(this);
        });
    });

    if (uploadInput) {
        uploadInput.addEventListener("change", function () {
            const file = this.files?.[0];
            if (!file || !file.type.startsWith("image/")) return;

            if (hint) {
                hint.textContent = "Đang tải ảnh và nhận diện môi...";
            }

            loadUploadedPreview(file);
        });
    }

    if (opacityRange) {
        opacityRange.addEventListener("input", handleOpacityChange);
    }

    window.addEventListener("resize", async function () {
        if (modal.style.display === "flex" && currentImageLoaded) {
            await waitForImageRender();
            resizeCanvasToImage();

            if (currentLandmarks) {
                drawLipMask(currentLandmarks);
            }
        }
    });

    const activeBtn = document.querySelector(".lip-color-btn.active") || colorButtons[0];
    if (activeBtn) {
        setActiveColor(activeBtn);
    }

    if (previewImage.complete && previewImage.naturalWidth > 0) {
        currentImageLoaded = true;
    }
});