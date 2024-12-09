import { parseLogWithSeverity } from '../../src/ts/utils/logsUtils';
import { SeverityClasses, LogEntry } from '../../src/ts/types/logsTypes';

describe('parseLogWithSeverity', () => {
  it('should parse a log with SUCCESS severity', () => {
    const log = 'DEBUG - Operation completed successfully';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: SeverityClasses.SUCCESS,
      message: 'Operation completed successfully'
    });
  });

  it('should parse a log with WARNING severity', () => {
    const log = 'WARNING - Disk space is low';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: SeverityClasses.WARNING,
      message: 'Disk space is low'
    });
  });

  it('should parse a log with ERROR severity', () => {
    const log = 'ERROR - System failure occurred';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: SeverityClasses.ERROR,
      message: 'System failure occurred'
    });
  });

  it('should return ERROR severity for invalid log formats', () => {
    const log = 'This is an invalid log format';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: SeverityClasses.ERROR,
      message: 'This is an invalid log format'
    });
  });

  it('should handle logs with extra spaces around severity', () => {
    const log = '  INFO   -   Operation completed  ';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: SeverityClasses.SUCCESS,
      message: 'Operation completed'
    });
  });

  it('should handle empty log strings gracefully', () => {
    const log = '';
    const result = parseLogWithSeverity(log);

    expect(result).toEqual<LogEntry>({
      severity: SeverityClasses.ERROR,
      message: ''
    });
  });
});
