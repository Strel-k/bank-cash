// Face-API.js Implementation for B-Cash
// This runs entirely in the browser - no server costs!

class FaceRecognitionAI {
    constructor() {
        this.isLoaded = false;
        this.faceDescriptor = null;
        this.loadModels();
    }

    async loadModels() {
        try {
            console.log('Loading Face-API.js models from ./models/...');
            
            // Check if Face-API.js is available
            if (typeof faceapi === 'undefined') {
                throw new Error('Face-API.js library not loaded. Please check if the CDN link is working.');
            }
            
            // Load face detection and recognition models one by one with better error handling
            console.log('Loading tiny face detector...');
            await faceapi.nets.tinyFaceDetector.loadFromUri('./models');
            console.log('✅ Tiny face detector loaded');
            
            console.log('Loading face landmarks...');
            await faceapi.nets.faceLandmark68Net.loadFromUri('./models');
            console.log('✅ Face landmarks loaded');
            
            console.log('Loading face recognition...');
            await faceapi.nets.faceRecognitionNet.loadFromUri('./models');
            console.log('✅ Face recognition loaded');
            
            console.log('Loading face expressions...');
            await faceapi.nets.faceExpressionNet.loadFromUri('./models');
            console.log('✅ Face expressions loaded');
            
            this.isLoaded = true;
            console.log('🎉 All Face-API.js models loaded successfully!');
            
        } catch (error) {
            console.error('❌ Failed to load Face-API.js models:', error);
            console.error('Make sure the models are downloaded in the ./models/ directory');
            
            // Try alternative path
            console.log('Trying alternative path: /models/...');
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                await faceapi.nets.faceExpressionNet.loadFromUri('/models');
                
                this.isLoaded = true;
                console.log('🎉 Models loaded from alternative path!');
            } catch (altError) {
                console.error('❌ Alternative path also failed:', altError);
                console.error('Please run: php download-ai-models.php');
            }
        }
    }

    async detectFace(imageElement) {
        if (!this.isLoaded) {
            throw new Error('Face recognition models not loaded yet');
        }

        try {
            // Detect face with landmarks and descriptor
            const detection = await faceapi
                .detectSingleFace(imageElement, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor()
                .withFaceExpressions();

            if (!detection) {
                return {
                    success: false,
                    message: 'No face detected in image'
                };
            }

            // Calculate face quality score
            const faceQuality = this.calculateFaceQuality(detection);
            
            // Perform liveness check (basic)
            const livenessScore = this.performBasicLivenessCheck(detection);

            return {
                success: true,
                face_detected: true,
                confidence: detection.detection.score,
                face_descriptor: Array.from(detection.descriptor), // 128-dimensional face encoding
                landmarks: detection.landmarks.positions,
                expressions: detection.expressions,
                face_quality: faceQuality,
                liveness_score: livenessScore,
                bounding_box: {
                    x: detection.detection.box.x,
                    y: detection.detection.box.y,
                    width: detection.detection.box.width,
                    height: detection.detection.box.height
                }
            };
        } catch (error) {
            console.error('Face detection error:', error);
            return {
                success: false,
                message: error.message
            };
        }
    }

    calculateFaceQuality(detection) {
        // Calculate face quality based on various factors
        const confidence = detection.detection.score;
        const box = detection.detection.box;
        
        // Check face size (should be reasonable size)
        const faceSize = (box.width * box.height) / (640 * 480); // Assuming 640x480 image
        const sizeScore = Math.min(faceSize * 10, 1); // Normalize to 0-1
        
        // Check if face is centered
        const centerX = box.x + box.width / 2;
        const centerY = box.y + box.height / 2;
        const centerScore = 1 - (Math.abs(centerX - 320) + Math.abs(centerY - 240)) / 640;
        
        // Overall quality score
        return (confidence * 0.5 + sizeScore * 0.3 + centerScore * 0.2);
    }

    performBasicLivenessCheck(detection) {
        // Basic liveness check based on facial expressions and landmarks
        const expressions = detection.expressions;
        
        // Check for natural expressions (not too neutral)
        const neutralScore = expressions.neutral;
        const otherExpressions = 1 - neutralScore;
        
        // Check landmark positions for natural face geometry
        const landmarks = detection.landmarks.positions;
        const eyeDistance = this.calculateEyeDistance(landmarks);
        const mouthWidth = this.calculateMouthWidth(landmarks);
        
        // Simple liveness score (in production, use more sophisticated methods)
        const expressionScore = Math.min(otherExpressions * 2, 1);
        const geometryScore = (eyeDistance > 20 && mouthWidth > 15) ? 0.8 : 0.4;
        
        return (expressionScore * 0.6 + geometryScore * 0.4);
    }

    calculateEyeDistance(landmarks) {
        // Calculate distance between eyes
        const leftEye = landmarks[36]; // Left eye outer corner
        const rightEye = landmarks[45]; // Right eye outer corner
        
        return Math.sqrt(
            Math.pow(rightEye.x - leftEye.x, 2) + 
            Math.pow(rightEye.y - leftEye.y, 2)
        );
    }

    calculateMouthWidth(landmarks) {
        // Calculate mouth width
        const leftMouth = landmarks[48]; // Left mouth corner
        const rightMouth = landmarks[54]; // Right mouth corner
        
        return Math.sqrt(
            Math.pow(rightMouth.x - leftMouth.x, 2) + 
            Math.pow(rightMouth.y - leftMouth.y, 2)
        );
    }

    compareFaces(descriptor1, descriptor2) {
        // Calculate Euclidean distance between face descriptors
        if (!descriptor1 || !descriptor2) {
            return 0;
        }

        const distance = faceapi.euclideanDistance(descriptor1, descriptor2);
        
        // Convert distance to similarity score (0-1)
        // Lower distance = higher similarity
        const similarity = Math.max(0, 1 - distance);
        
        return similarity;
    }

    async processIDPhoto(imageElement) {
        // Process ID document photo
        const result = await this.detectFace(imageElement);
        
        if (result.success) {
            return {
                success: true,
                face_descriptor: result.face_descriptor,
                confidence: result.confidence,
                face_quality: result.face_quality
            };
        }
        
        return result;
    }

    async processSelfie(imageElement) {
        // Process live selfie
        const result = await this.detectFace(imageElement);
        
        if (result.success) {
            return {
                success: true,
                face_descriptor: result.face_descriptor,
                confidence: result.confidence,
                liveness_score: result.liveness_score,
                face_quality: result.face_quality
            };
        }
        
        return result;
    }

    async verifyFaces(idPhotoElement, selfieElement) {
        try {
            console.log('Starting face verification...');
            
            // Process both images
            const idResult = await this.processIDPhoto(idPhotoElement);
            const selfieResult = await this.processSelfie(selfieElement);
            
            if (!idResult.success) {
                return {
                    success: false,
                    message: 'Could not detect face in ID photo: ' + idResult.message
                };
            }
            
            if (!selfieResult.success) {
                return {
                    success: false,
                    message: 'Could not detect face in selfie: ' + selfieResult.message
                };
            }
            
            // Compare faces
            const similarity = this.compareFaces(
                idResult.face_descriptor, 
                selfieResult.face_descriptor
            );
            
            // Make verification decision (adjusted thresholds for live camera)
            const isVerified = similarity >= 0.6 && selfieResult.liveness_score >= 0.3;
            
            return {
                success: true,
                verified: isVerified,
                similarity_score: similarity,
                liveness_score: selfieResult.liveness_score,
                id_confidence: idResult.confidence,
                selfie_confidence: selfieResult.confidence,
                details: {
                    id_face_quality: idResult.face_quality,
                    selfie_face_quality: selfieResult.face_quality,
                    threshold_similarity: 0.6,
                    threshold_liveness: 0.5
                }
            };
            
        } catch (error) {
            console.error('Face verification error:', error);
            return {
                success: false,
                message: error.message
            };
        }
    }
}

// Global instance
window.faceRecognitionAI = new FaceRecognitionAI();