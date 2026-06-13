const ADULT_PATTERN = /(adult|18\s*\+|xxx|x{3,}|porn|hot|mature|erotic)/i;

export function isAdultName(name: string): boolean {
  return ADULT_PATTERN.test(name);
}
