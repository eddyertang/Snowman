import math
import random
from PIL import Image, ImageDraw

W, H = 400, 500
N_FRAMES = 24
random.seed(42)

# Snowflakes: (x, y0, speed, radius)
flakes = []
for _ in range(60):
    x = random.uniform(0, W)
    y0 = random.uniform(0, H)
    speed = random.uniform(1.5, 4.0)
    r = random.uniform(1.5, 3.5)
    flakes.append([x, y0, speed, r])


def draw_sky(draw):
    top = (176, 213, 240)
    bottom = (230, 244, 250)
    for y in range(H):
        t = y / H
        c = tuple(int(top[i] + (bottom[i] - top[i]) * t) for i in range(3))
        draw.line([(0, y), (W, y)], fill=c)


def draw_ground(draw):
    draw.rectangle([0, H - 70, W, H], fill=(245, 250, 253))
    draw.ellipse([-40, H - 90, W + 40, H - 40], fill=(255, 255, 255))


def draw_tree(draw, x, y, scale=1.0):
    trunk_w = int(10 * scale)
    trunk_h = int(20 * scale)
    draw.rectangle([x - trunk_w // 2, y, x + trunk_w // 2, y + trunk_h], fill=(90, 60, 40))
    green = (46, 111, 64)
    for i, s in enumerate([70, 55, 40]):
        s = int(s * scale)
        top_y = y - i * int(28 * scale)
        draw.polygon(
            [(x, top_y - s), (x - s, top_y), (x + s, top_y)],
            fill=green,
        )


def draw_snowman(draw, t):
    cx = W // 2
    # body circles: bottom, middle, head
    bottom_r = 75
    bottom_cy = H - 130
    mid_r = 55
    mid_cy = bottom_cy - bottom_r - mid_r + 15
    head_r = 38
    head_cy = mid_cy - mid_r - head_r + 12

    def circle(cy, r, fill=(255, 255, 255), outline=(200, 210, 220)):
        draw.ellipse([cx - r, cy - r, cx + r, cy + r], fill=fill, outline=outline, width=2)

    # shadow
    draw.ellipse([cx - bottom_r - 5, bottom_cy + bottom_r - 15,
                  cx + bottom_r + 5, bottom_cy + bottom_r + 15], fill=(210, 225, 235))

    circle(bottom_cy, bottom_r)
    circle(mid_cy, mid_r)

    # arms (sticks) - left static, right waves
    arm_len = 70
    lx, ly = cx - mid_r + 10, mid_cy - 5
    ang_l = math.radians(160)
    draw.line([(lx, ly), (lx + arm_len * math.cos(ang_l), ly - arm_len * math.sin(ang_l))],
              fill=(90, 60, 40), width=6)
    # small twig
    ex, ey = lx + arm_len * math.cos(ang_l), ly - arm_len * math.sin(ang_l)
    draw.line([(ex, ey), (ex - 12, ey - 10)], fill=(90, 60, 40), width=4)

    rx, ry = cx + mid_r - 10, mid_cy - 5
    wave = math.sin(t * 2 * math.pi) * 18
    ang_r = math.radians(20 + wave)
    ex2 = rx + arm_len * math.cos(ang_r)
    ey2 = ry - arm_len * math.sin(ang_r)
    draw.line([(rx, ry), (ex2, ey2)], fill=(90, 60, 40), width=6)
    draw.line([(ex2, ey2), (ex2 + 10, ey2 - 12)], fill=(90, 60, 40), width=4)
    draw.line([(ex2, ey2), (ex2 - 4, ey2 - 14)], fill=(90, 60, 40), width=4)

    # buttons
    for i in range(3):
        by = mid_cy - 20 + i * 18
        draw.ellipse([cx - 6, by - 6, cx + 6, by + 6], fill=(40, 40, 40))

    circle(head_cy, head_r)

    # scarf
    draw.polygon([
        (cx - head_r + 5, head_cy + head_r - 10),
        (cx + head_r - 5, head_cy + head_r - 10),
        (cx + head_r - 5, head_cy + head_r + 8),
        (cx, head_cy + head_r - 2),
        (cx - head_r + 5, head_cy + head_r + 8),
    ], fill=(200, 40, 40))
    draw.polygon([
        (cx + 8, head_cy + head_r),
        (cx + 26, head_cy + head_r + 30),
        (cx + 14, head_cy + head_r + 32),
        (cx + 2, head_cy + head_r + 6),
    ], fill=(170, 30, 30))

    # face: blinking eyes
    blink = (t % 1.0) > 0.93
    eye_dy = -6
    if blink:
        draw.line([(cx - 16, head_cy + eye_dy), (cx - 8, head_cy + eye_dy)], fill=(30, 30, 30), width=3)
        draw.line([(cx + 8, head_cy + eye_dy), (cx + 16, head_cy + eye_dy)], fill=(30, 30, 30), width=3)
    else:
        draw.ellipse([cx - 16, head_cy + eye_dy - 4, cx - 8, head_cy + eye_dy + 4], fill=(30, 30, 30))
        draw.ellipse([cx + 8, head_cy + eye_dy - 4, cx + 16, head_cy + eye_dy + 4], fill=(30, 30, 30))

    # carrot nose
    draw.polygon([
        (cx, head_cy + 2),
        (cx + 26, head_cy + 7),
        (cx, head_cy + 12),
    ], fill=(240, 120, 30))

    # smile (coal dots)
    for i, dx in enumerate([-14, -6, 2, 10, 18]):
        yy = head_cy + 16 + int(2 * math.sin(i * 0.8))
        draw.ellipse([cx + dx - 2, yy - 2, cx + dx + 2, yy + 2], fill=(30, 30, 30))

    # hat
    brim_y = head_cy - head_r + 6
    draw.rectangle([cx - head_r + 2, brim_y - 4, cx + head_r - 2, brim_y + 4], fill=(20, 20, 20))
    hat_w = head_r * 0.9
    hat_h = 46
    draw.rectangle([cx - hat_w, brim_y - hat_h, cx + hat_w, brim_y], fill=(20, 20, 20))
    draw.rectangle([cx - hat_w, brim_y - 14, cx + hat_w, brim_y - 4], fill=(200, 40, 40))


def draw_snow(draw, frame_idx):
    for f in flakes:
        x, y0, speed, r = f
        y = (y0 + frame_idx * speed) % (H + 10) - 10
        draw.ellipse([x - r, y - r, x + r, y + r], fill=(255, 255, 255))


frames = []
for i in range(N_FRAMES):
    t = i / N_FRAMES
    img = Image.new("RGB", (W, H))
    draw = ImageDraw.Draw(img)
    draw_sky(draw)
    draw_ground(draw)
    draw_tree(draw, 55, H - 95, scale=1.1)
    draw_tree(draw, W - 50, H - 85, scale=0.9)
    draw_snowman(draw, t)
    draw_snow(draw, i)
    frames.append(img)

out_path = "/home/user/Snowman/snowman.gif"
frames[0].save(
    out_path,
    save_all=True,
    append_images=frames[1:],
    duration=80,
    loop=0,
    optimize=False,
)
print("saved", out_path)
