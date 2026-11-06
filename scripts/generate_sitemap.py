#!/usr/bin/env python3
"""
scripts/generate_sitemap.py
Gera o sitemap.xml canônico do Via Scriptura (https://www.viascriptura.com/)
em conformidade com o protocolo Sitemaps 0.9 e extensão de Imagens do Google.

Inclui:
- Páginas institucionais e principais do portal
- Todos os 66 livros e 1.189 capítulos da Bíblia Sagrada (NAA)
- Todas as 366 histórias devocionais diárias (com imagens)
- Todas as 220 histórias bíblicas canônicas adicionais (com imagens)
Total: 1.781 URLs indexadas.
"""

from datetime import datetime
import json
import os
import xml.etree.ElementTree as ET

ROOT_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA_DIR = os.path.join(ROOT_DIR, 'data')
OUTPUT_FILE = os.path.join(ROOT_DIR, 'sitemap.xml')
BASE_URL = 'https://www.viascriptura.com'
TODAY = datetime.now().strftime('%Y-%m-%d')

CATEGORY_ARTWORK = {
    'Origens': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg',
    'Patriarcas': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jacob_esau_meeting.png',
    'Êxodo': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_sarca_ardente.png',
    'Lei e Aliança': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_consagracao_dos_sacerdotes.png',
    'Deserto': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/balaao_e_a_jumenta.png',
    'Conquista': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_morte_de_moises.png',
    'Juízes': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Viagem sagrada sob o luar.png',
    'Redenção': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_alegria_da_salvacao.png',
    'Reino Unido': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/ai-generated-8930616_1280.png',
    'Reino Dividido': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/O pregador e a figura luminosa.png',
    'Crônicas': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_apresentacao_no_templo.png',
    'Pós-Exílio': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jesus_no_templo_aos_doze_anos.png',
    'Sabedoria': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jesus_cresce_em_sabedoria.png',
    'Salmos': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_alegria_da_salvacao.png',
    'Profetas': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/um_menino_nos_nasceu.png',
    'Exílio': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/sodoma_gomorra_1280x800.png',
    'Evangelhos': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Batismo ao pôr do sol.png',
    'Parábolas': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jesus_cresce_em_sabedoria.png',
    'Milagres': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/O pregador e a figura luminosa.png',
    'Paixão': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Viagem sagrada sob o luar.png',
    'Ressurreição': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Cena de Natal iluminada e serena.png',
    'Igreja Primitiva': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/O pregador e a figura luminosa.png',
    'Epístolas': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg',
    'Apocalipse': 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Cena de Natal iluminada e serena.png',
}

def generate_sitemap() -> None:
    ET.register_namespace('', 'http://www.sitemaps.org/schemas/sitemap/0.9')
    ET.register_namespace('image', 'http://www.google.com/schemas/sitemap-image/1.1')

    urlset = ET.Element('{http://www.sitemaps.org/schemas/sitemap/0.9}urlset')

    def add_url(
        loc: str,
        priority: float,
        changefreq: str,
        lastmod: str = TODAY,
        img_url: str = None,
        img_title: str = None
    ) -> None:
        u = ET.SubElement(urlset, '{http://www.sitemaps.org/schemas/sitemap/0.9}url')
        l = ET.SubElement(u, '{http://www.sitemaps.org/schemas/sitemap/0.9}loc')
        l.text = loc
        lm = ET.SubElement(u, '{http://www.sitemaps.org/schemas/sitemap/0.9}lastmod')
        lm.text = lastmod
        cf = ET.SubElement(u, '{http://www.sitemaps.org/schemas/sitemap/0.9}changefreq')
        cf.text = changefreq
        p = ET.SubElement(u, '{http://www.sitemaps.org/schemas/sitemap/0.9}priority')
        p.text = f'{priority:.1f}'

        if img_url and str(img_url).strip():
            img_elem = ET.SubElement(u, '{http://www.google.com/schemas/sitemap-image/1.1}image')
            img_loc = ET.SubElement(img_elem, '{http://www.google.com/schemas/sitemap-image/1.1}loc')
            img_loc.text = str(img_url).strip()
            if img_title and str(img_title).strip():
                img_tit = ET.SubElement(img_elem, '{http://www.google.com/schemas/sitemap-image/1.1}title')
                img_tit.text = str(img_title).strip()

    # 1. Páginas Principais
    add_url(f'{BASE_URL}/', priority=1.0, changefreq='daily',
            img_url='https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg',
            img_title='Via Scriptura — Leitura Bíblica Diária Ilustrada')
    add_url(f'{BASE_URL}/bible.php', priority=0.9, changefreq='weekly')
    add_url(f'{BASE_URL}/stories.php', priority=0.9, changefreq='weekly')
    add_url(f'{BASE_URL}/divisions.php', priority=0.8, changefreq='weekly')
    add_url(f'{BASE_URL}/about.php', priority=0.6, changefreq='monthly')
    add_url(f'{BASE_URL}/privacy.php', priority=0.3, changefreq='monthly')

    # 2. Capítulos da Bíblia Completa (66 Livros · 1.189 Capítulos)
    books_file = os.path.join(DATA_DIR, 'bible', 'naa', 'books.json')
    if os.path.isfile(books_file):
        with open(books_file, 'r', encoding='utf-8') as f:
            books = json.load(f)

        for b in books:
            abbrev = b.get('abbrev', '')
            url_abbrev = 'job' if abbrev == 'jó' else abbrev
            total_chapters = int(b.get('chapters_count', 1))
            for ch in range(1, total_chapters + 1):
                loc = f'{BASE_URL}/bible.php?book={url_abbrev}&ch={ch}'
                add_url(loc, priority=0.7, changefreq='monthly')

    # 3. Histórias Bíblicas Diárias (366 Histórias Devocionais)
    daily_file = os.path.join(DATA_DIR, 'data.json')
    if os.path.isfile(daily_file):
        with open(daily_file, 'r', encoding='utf-8') as f:
            daily_stories = json.load(f)

        for s in daily_stories:
            slug = s.get('slug')
            if not slug:
                continue
            title = s.get('titulo', '')
            ref = s.get('referencia', '')
            full_title = f'{title} ({ref})' if ref else title
            img_info = s.get('imagem', {})
            img_url = img_info.get('url') if isinstance(img_info, dict) else None
            loc = f'{BASE_URL}/?p={slug}'
            add_url(loc, priority=0.8, changefreq='monthly', img_url=img_url, img_title=full_title)

    # 4. Histórias Bíblicas Canônicas Adicionais (220 Histórias)
    canonical_file = os.path.join(DATA_DIR, 'canonical_stories.json')
    if os.path.isfile(canonical_file):
        with open(canonical_file, 'r', encoding='utf-8') as f:
            canonical_stories = json.load(f)

        for idx, cs in enumerate(canonical_stories):
            cid = str(cs.get('id', f'c{idx + 1}'))
            slug = f'canonical_{cid}'
            title = cs.get('titulo', '')
            ref = cs.get('referencia', '')
            full_title = f'{title} ({ref})' if ref else title
            cat = cs.get('categoria', 'Origens')
            img_url = CATEGORY_ARTWORK.get(cat, CATEGORY_ARTWORK['Origens'])
            loc = f'{BASE_URL}/?p={slug}'
            add_url(loc, priority=0.7, changefreq='monthly', img_url=img_url, img_title=full_title)

    ET.indent(urlset, space='  ')
    xml_header = '<?xml version="1.0" encoding="UTF-8"?>\n'
    xml_body = ET.tostring(urlset, encoding='utf-8').decode('utf-8')

    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        f.write(xml_header + xml_body + '\n')

    total_count = len(urlset)
    file_size_kb = os.path.getsize(OUTPUT_FILE) / 1024
    print(f'Sitemap gerado com sucesso em: {OUTPUT_FILE}')
    print(f'Total de URLs indexadas: {total_count}')
    print(f'Tamanho do arquivo: {file_size_kb:.2f} KB')

if __name__ == '__main__':
    generate_sitemap()
