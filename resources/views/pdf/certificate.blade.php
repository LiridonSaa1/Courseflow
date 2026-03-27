<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; padding: 40px; }
        h1 { color: #2563eb; }
        .meta { margin-top: 24px; font-size: 12px; color: #444; }
    </style>
</head>
<body>
    <h1>Certificate of Completion</h1>
    <p>This certifies that</p>
    <h2>{{ $name }}</h2>
    <p>successfully completed</p>
    <h3>{{ $course }}</h3>
    <p>Score: {{ $score }}%</p>
    <div class="meta">Verify: {{ $token }}</div>
    <div style="margin-top: 20px;">{!! $qrSvg !!}</div>
</body>
</html>
