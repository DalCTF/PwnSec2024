import hashlib
import string
from pathlib import Path
import os

current_dir = Path(os.path.dirname(os.path.abspath(__file__)))
filepath = (current_dir / "../Source/pieces.txt").resolve()
hashes = open(filepath, "r+").readlines()

letters = string.printable[:-6]
so_far = ""

for hash in hashes:
    hash = hash.strip()

    for letter in letters:
        candidate = so_far + letter
        candidate = hashlib.sha256(candidate.encode()).hexdigest()

        if candidate == hash:
            so_far += letter
            print(f"{so_far:>37s} : {candidate}")
            break
