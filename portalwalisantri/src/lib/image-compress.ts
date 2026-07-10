/**
 * Client-side image compression utility using HTML5 Canvas.
 * Recursively resizes and lowers the quality of the image until the file size is under the specified limit (default 300KB).
 */
export async function compressImage(file: File, maxSizeBytes: number = 300 * 1024): Promise<File> {
  // If the file is already smaller than the target size, return it as-is
  if (file.size <= maxSizeBytes) {
    console.log(`Image size is already within limit: ${(file.size / 1024).toFixed(1)} KB`);
    return file;
  }

  // Only compress standard image types
  if (!file.type.startsWith("image/")) {
    return file;
  }

  console.log(`Compressing image ${file.name} of size ${(file.size / 1024).toFixed(1)} KB to under ${maxSizeBytes / 1024} KB`);

  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        // Attempts with different dimensions and quality levels to budget under 300KB
        const attempts = [
          { maxDim: 1600, quality: 0.85 },
          { maxDim: 1200, quality: 0.70 },
          { maxDim: 950, quality: 0.60 },
          { maxDim: 800, quality: 0.45 },
          { maxDim: 640, quality: 0.35 },
        ];

        let currentAttemptIndex = 0;

        const tryCompress = () => {
          if (currentAttemptIndex >= attempts.length) {
            // Last resort: compress using the lowest quality and size settings
            const finalAttempt = attempts[attempts.length - 1];
            performCompression(finalAttempt.maxDim, finalAttempt.quality);
            return;
          }

          const { maxDim, quality } = attempts[currentAttemptIndex];
          performCompression(maxDim, quality);
        };

        const performCompression = (maxDim: number, quality: number) => {
          const canvas = document.createElement("canvas");
          let width = img.width;
          let height = img.height;

          // Downscale dimension proportionally if it exceeds maximum boundary
          if (width > maxDim || height > maxDim) {
            if (width > height) {
              height = Math.round((height * maxDim) / width);
              width = maxDim;
            } else {
              width = Math.round((width * maxDim) / height);
              height = maxDim;
            }
          }

          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext("2d");
          if (!ctx) {
            reject(new Error("Could not get 2d context from canvas"));
            return;
          }

          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob(
            (blob) => {
              if (!blob) {
                reject(new Error("Canvas to blob conversion failed"));
                return;
              }

              console.log(`Attempt ${currentAttemptIndex + 1} (MaxDim: ${maxDim}, Quality: ${quality}): Result size = ${(blob.size / 1024).toFixed(1)} KB`);

              if (blob.size <= maxSizeBytes || currentAttemptIndex >= attempts.length - 1) {
                // Done: Wrap blob in a File object
                const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                  type: "image/jpeg",
                  lastModified: Date.now(),
                });
                resolve(compressedFile);
              } else {
                // Try next step down
                currentAttemptIndex++;
                tryCompress();
              }
            },
            "image/jpeg",
            quality
          );
        };

        tryCompress();
      };
      img.onerror = () => reject(new Error("Failed to load image"));
      img.src = e.target?.result as string;
    };
    reader.onerror = () => reject(new Error("FileReader failed"));
    reader.readAsDataURL(file);
  });
}
